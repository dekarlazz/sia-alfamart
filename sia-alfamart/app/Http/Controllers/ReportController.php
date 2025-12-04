<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function jurnal()
    {
        $entries = JournalEntry::with(['transaction', 'account'])
            ->get()
            ->sortByDesc('transaction.date'); 
            
        return view('reports.jurnal', compact('entries'));
    }

    public function bukuBesar()
    {
        $accounts = Account::with(['journalEntries.transaction'])->get();
        return view('reports.buku_besar', compact('accounts'));
    }

    public function keuangan()
    {
        // --- 1. LAPORAN LABA RUGI (INCOME STATEMENT) ---
        // Pendapatan (Saldo Normal: Kredit)
        $pendapatan = Account::where('type', 'penghasilan')->get();
        $totalPendapatan = $pendapatan->sum(fn($a) => $a->journalEntries->sum('credit') - $a->journalEntries->sum('debit'));

        // Beban (Saldo Normal: Debit)
        $beban = Account::where('type', 'beban')->get();
        $totalBeban = $beban->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));
        
        // Laba Bersih
        $labaBersih = $totalPendapatan - $totalBeban;


        // --- 2. LAPORAN PERUBAHAN EKUITAS (STATEMENT OF CHANGES IN EQUITY) ---
        // Modal Saham (Saldo Normal: Kredit)
        $modalAccount = Account::where('code', '301')->first();
        $modalAwal = $modalAccount ? $modalAccount->journalEntries->sum('credit') - $modalAccount->journalEntries->sum('debit') : 0;
        
        // Dividen (Saldo Normal: Debit)
        // Dividen mengurangi Ekuitas
        $dividenAccount = Account::where('code', '302')->first();
        $dividen = $dividenAccount ? $dividenAccount->journalEntries->sum('debit') - $dividenAccount->journalEntries->sum('credit') : 0;
        
        // Ekuitas Akhir = Modal Awal + Laba Bersih - Dividen
        $ekuitasAkhir = $modalAwal + $labaBersih - $dividen;


        // --- 3. NERACA (BALANCE SHEET) ---
        // Aset (Saldo Normal: Debit)
        $aset = Account::where('type', 'aset')->get();
        $totalAset = $aset->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));

        // Liabilitas (Saldo Normal: Kredit)
        $liabilitas = Account::where('type', 'liabilitas')->get();
        $totalLiabilitas = $liabilitas->sum(fn($a) => $a->journalEntries->sum('credit') - $a->journalEntries->sum('debit'));

        // Total Pasiva (Liabilitas + Ekuitas Akhir)
        $totalPasiva = $totalLiabilitas + $ekuitasAkhir;

        // Pengecekan Balance
        $isBalanced = ($totalAset == $totalPasiva);

        return view('reports.keuangan', compact(
            'pendapatan', 'totalPendapatan', 
            'beban', 'totalBeban', 
            'labaBersih',
            'modalAwal', 'dividen', 'ekuitasAkhir',
            'aset', 'totalAset', 
            'liabilitas', 'totalLiabilitas', 'totalPasiva', 'isBalanced'
        ));
    }
}