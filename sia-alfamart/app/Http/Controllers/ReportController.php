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
        // --- 1. LAPORAN LABA RUGI ---
        $pendapatan = Account::where('type', 'penghasilan')->get();
        $totalPendapatan = $pendapatan->sum(fn($a) => $a->journalEntries->sum('credit') - $a->journalEntries->sum('debit'));

        $beban = Account::where('type', 'beban')->get();
        $totalBeban = $beban->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));
        
        $labaBersih = $totalPendapatan - $totalBeban;

        // --- 2. PERUBAHAN EKUITAS ---
        $modalAccount = Account::where('code', '301')->first();
        $modalAwal = $modalAccount ? $modalAccount->journalEntries->sum('credit') - $modalAccount->journalEntries->sum('debit') : 0;
        
        $dividenAccount = Account::where('code', '302')->first();
        $dividen = $dividenAccount ? $dividenAccount->journalEntries->sum('debit') - $dividenAccount->journalEntries->sum('credit') : 0;
        
        $ekuitasAkhir = $modalAwal + $labaBersih - $dividen;

        // --- 3. NERACA ---
        $aset = Account::where('type', 'aset')->get();
        $totalAset = $aset->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));

        $liabilitas = Account::where('type', 'liabilitas')->get();
        $totalLiabilitas = $liabilitas->sum(fn($a) => $a->journalEntries->sum('credit') - $a->journalEntries->sum('debit'));

        $totalPasiva = $totalLiabilitas + $ekuitasAkhir;
        $isBalanced = ($totalAset == $totalPasiva);

        // Data Grafik Aset
        $asetLancar = Account::whereIn('code', ['101', '102'])->get()
            ->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));
        $asetTetap = Account::whereIn('code', ['103'])->get()
            ->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));

        // --- 4. ANALISIS RASIO KEUANGAN ---
        // A. Net Profit Margin (NPM) = Laba Bersih / Pendapatan
        $npm = $totalPendapatan > 0 ? ($labaBersih / $totalPendapatan) * 100 : 0;

        // B. Debt to Asset Ratio (DAR) = Total Utang / Total Aset
        $dar = $totalAset > 0 ? ($totalLiabilitas / $totalAset) * 100 : 0;

        // C. Return on Equity (ROE) = Laba Bersih / Ekuitas
        $roe = $ekuitasAkhir > 0 ? ($labaBersih / $ekuitasAkhir) * 100 : 0;

        return view('reports.keuangan', compact(
            'pendapatan', 'totalPendapatan', 'beban', 'totalBeban', 'labaBersih',
            'modalAwal', 'dividen', 'ekuitasAkhir',
            'aset', 'totalAset', 'liabilitas', 'totalLiabilitas', 'totalPasiva', 'isBalanced',
            'asetLancar', 'asetTetap',
            'npm', 'dar', 'roe' // Kirim data rasio ke view
        ));
    }
}