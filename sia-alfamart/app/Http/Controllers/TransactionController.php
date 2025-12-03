<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        // 1. Ambil data transaksi terbaru
        $transactions = Transaction::orderBy('date', 'desc')->get();
        
        // 2. Hitung Saldo Kas (Akun 101)
        // Rumus: Total Debit - Total Kredit
        $kasAcc = Account::where('code', '101')->first();
        $totalKas = 0;
        if($kasAcc) {
            $totalKas = $kasAcc->journalEntries->sum('debit') - $kasAcc->journalEntries->sum('credit');
        }

        // 3. Hitung Pendapatan & Beban
        $pendapatan = JournalEntry::whereHas('account', fn($q) => $q->where('type', 'penghasilan'))->sum('credit');
        $beban = JournalEntry::whereHas('account', fn($q) => $q->where('type', 'beban'))->sum('debit');

        // 4. Siapkan Data Grafik (Arus Kas Harian)
        $dailyCash = DB::table('journal_entries')
            ->join('transactions', 'journal_entries.transaction_id', '=', 'transactions.id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.code', '101') // Hanya akun Kas
            ->selectRaw('transactions.date, sum(debit) as masuk, sum(credit) as keluar')
            ->groupBy('transactions.date')
            ->orderBy('transactions.date')
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'totalKas' => $totalKas,
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'chartLabels' => $dailyCash->pluck('date'),
            'chartMasuk' => $dailyCash->pluck('masuk'),
            'chartKeluar' => $dailyCash->pluck('keluar')
        ]);
    }

    public function store(Request $request)
    {
        // Simpan Transaksi Baru
        $trx = Transaction::create($request->all());
        $this->createJournals($trx); // Panggil robot jurnal
        return redirect()->back()->with('success', 'Transaksi berhasil disimpan!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        // Update Transaksi
        $transaction->update($request->all());
        $transaction->journalEntries()->delete(); // Hapus jurnal lama
        $this->createJournals($transaction); // Bikin jurnal baru
        return redirect()->route('dashboard')->with('success', 'Transaksi diperbarui!');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->back()->with('success', 'Transaksi dihapus!');
    }

    public function edit(Transaction $transaction)
    {
        return view('transactions.edit', compact('transaction'));
    }

    // --- LOGIC ROBOT JURNAL ---
    private function createJournals($trx)
    {
        $dr = ''; $cr = '';
        
        // Pasangan Akun (Debit / Kredit)
        switch ($trx->type) {
            case 'penjualan': $dr='101'; $cr='401'; break; // Kas - Pendapatan
            case 'pembelian_persediaan': $dr='102'; $cr='101'; break; // Stok - Kas
            case 'beban_ops': $dr='502'; $cr='101'; break; // Beban Ops - Kas
            case 'beban_gaji': $dr='503'; $cr='101'; break; // Gaji - Kas
            case 'hpp': $dr='501'; $cr='102'; break; // HPP - Stok
            case 'pendapatan_lain': $dr='101'; $cr='402'; break; // Kas - Pendapatan Lain
            case 'dividen': $dr='303'; $cr='101'; break; // Dividen - Kas
            case 'capex': $dr='103'; $cr='101'; break; // Aset Tetap - Kas
        }

        if ($dr && $cr) {
            // Masukkan Debit
            JournalEntry::create([
                'transaction_id' => $trx->id,
                'account_id' => Account::where('code', $dr)->first()->id,
                'debit' => $trx->amount, 'credit' => 0
            ]);
            // Masukkan Kredit
            JournalEntry::create([
                'transaction_id' => $trx->id,
                'account_id' => Account::where('code', $cr)->first()->id,
                'debit' => 0, 'credit' => $trx->amount
            ]);
        }
    }
}