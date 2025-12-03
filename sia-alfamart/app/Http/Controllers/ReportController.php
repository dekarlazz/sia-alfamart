<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function jurnal()
    {
        // Mengambil data jurnal beserta relasi transaksi & akun
        $entries = JournalEntry::with(['transaction', 'account'])
            ->get()
            ->sortByDesc('transaction.date'); 
            
        return view('reports.jurnal', compact('entries'));
    }

    public function bukuBesar()
    {
        // Mengambil data akun beserta relasi jurnalnya
        $accounts = Account::with(['journalEntries.transaction'])->get();
        return view('reports.buku_besar', compact('accounts'));
    }

    public function keuangan()
    {
        // 1. Ambil Akun per Kategori
        $pendapatan = Account::where('type', 'penghasilan')->get();
        $beban = Account::where('type', 'beban')->get();
        $aset = Account::where('type', 'aset')->get();
        $liabilitas = Account::where('type', 'liabilitas')->get();
        
        // 2. Hitung Total Laba Rugi
        // (Kredit - Debit untuk Pendapatan)
        $totalPendapatan = $pendapatan->sum(fn($a) => $a->journalEntries->sum('credit') - $a->journalEntries->sum('debit'));
        // (Debit - Kredit untuk Beban)
        $totalBeban = $beban->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));
        
        $labaBersih = $totalPendapatan - $totalBeban;

        // 3. Hitung Ekuitas Akhir (Modal Awal + Laba - Dividen)
        // Ambil saldo kredit akun modal (301)
        $modalAccount = Account::where('code', '301')->first();
        $modalAwal = $modalAccount ? $modalAccount->journalEntries->sum('credit') : 0;
        
        // Ambil saldo debit akun dividen (303)
        $dividenAccount = Account::where('code', '303')->first();
        $dividen = $dividenAccount ? $dividenAccount->journalEntries->sum('debit') : 0;
        
        $ekuitasAkhir = $modalAwal + $labaBersih - $dividen;

        // 4. Hitung Neraca
        $totalAset = $aset->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));
        $totalLiabilitas = $liabilitas->sum(fn($a) => $a->journalEntries->sum('credit') - $a->journalEntries->sum('debit'));

        return view('reports.keuangan', compact(
            'totalPendapatan', 'totalBeban', 'labaBersih',
            'totalAset', 'totalLiabilitas', 'ekuitasAkhir'
        ));
    }
}