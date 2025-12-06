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

        $asetLancar = Account::whereIn('code', ['101', '102'])->get()->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));
        $asetTetap = Account::whereIn('code', ['103'])->get()->sum(fn($a) => $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'));

        // --- 4. LAPORAN ARUS KAS (CASH FLOW STATEMENT) ---
        
        // A. Arus Kas dari Aktivitas Operasi
        // Masuk: Penjualan, Pendapatan Lain
        $kasMasukOperasi = \App\Models\Transaction::whereIn('type', ['penjualan', 'pendapatan_lain'])->sum('amount');
        // Keluar: Pembelian Tunai, Pelunasan Utang, Beban Ops, Gaji, Pajak
        $kasKeluarOperasi = \App\Models\Transaction::whereIn('type', ['pembelian_tunai', 'pelunasan_utang', 'beban_ops', 'beban_gaji', 'beban_pajak'])->sum('amount');
        $kasBersihOperasi = $kasMasukOperasi - $kasKeluarOperasi;

        // B. Arus Kas dari Aktivitas Investasi
        // Keluar: Capex (Beli Aset)
        $kasKeluarInvestasi = \App\Models\Transaction::where('type', 'capex')->sum('amount');
        $kasBersihInvestasi = -1 * $kasKeluarInvestasi; // Pasti minus kalau beli aset

        // C. Arus Kas dari Aktivitas Pendanaan
        // Masuk: Utang Bank
        $kasMasukPendanaan = \App\Models\Transaction::where('type', 'utang_bank')->sum('amount');
        // Keluar: Dividen
        $kasKeluarPendanaan = \App\Models\Transaction::where('type', 'dividen')->sum('amount');
        $kasBersihPendanaan = $kasMasukPendanaan - $kasKeluarPendanaan;

        // Total Kenaikan/Penurunan Kas
        $kenaikanKas = $kasBersihOperasi + $kasBersihInvestasi + $kasBersihPendanaan;

        // Saldo Awal Kas (Diambil dari Jurnal Saldo Awal akun 101)
        $saldoAwalKas = JournalEntry::whereHas('account', fn($q) => $q->where('code', '101'))
            ->whereHas('transaction', fn($q) => $q->where('type', 'saldo_awal'))
            ->sum('debit');

        $saldoAkhirKas = $saldoAwalKas + $kenaikanKas;

        // --- 5. RASIO ---
        $npm = $totalPendapatan > 0 ? ($labaBersih / $totalPendapatan) * 100 : 0;
        $dar = $totalAset > 0 ? ($totalLiabilitas / $totalAset) * 100 : 0;
        $roe = $ekuitasAkhir > 0 ? ($labaBersih / $ekuitasAkhir) * 100 : 0;

        return view('reports.keuangan', compact(
            'pendapatan', 'totalPendapatan', 'beban', 'totalBeban', 'labaBersih',
            'modalAwal', 'dividen', 'ekuitasAkhir',
            'aset', 'totalAset', 'liabilitas', 'totalLiabilitas', 'totalPasiva', 'isBalanced',
            'asetLancar', 'asetTetap',
            'kasBersihOperasi', 'kasBersihInvestasi', 'kasBersihPendanaan', 'kenaikanKas', 'saldoAwalKas', 'saldoAkhirKas',
            'npm', 'dar', 'roe'
        ));
    }
}