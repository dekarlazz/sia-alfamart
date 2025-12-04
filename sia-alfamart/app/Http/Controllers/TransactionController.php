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
        
        // 2. Hitung Saldo Kas 
        $kasAcc = Account::where('code', '101')->first();
        $totalKas = 0;
        
        if($kasAcc) {
            // Rumus: Sum(Debit - Kredit)
            $totalKas = JournalEntry::where('account_id', $kasAcc->id)
                ->sum(DB::raw('debit - credit'));
        }

        // 3. Hitung Pendapatan & Beban (Tetap Kredit/Debit murni)
        $pendapatan = JournalEntry::whereHas('account', fn($q) => $q->where('type', 'penghasilan'))->sum('credit');
        $beban = JournalEntry::whereHas('account', fn($q) => $q->where('type', 'beban'))->sum('debit');

        // 4. Data Grafik
        $dailyCash = DB::table('journal_entries')
            ->join('transactions', 'journal_entries.transaction_id', '=', 'transactions.id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->where('accounts.code', '101') 
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
        $trx = Transaction::create($request->all());
        $this->createJournals($trx);
        return redirect()->back()->with('success', 'Transaksi berhasil disimpan!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $transaction->update($request->all());
        $transaction->journalEntries()->delete();
        $this->createJournals($transaction);
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

    private function createJournals($trx)
    {
        $dr = ''; $cr = '';
        switch ($trx->type) {
            case 'penjualan': 
                $dr='101'; $cr='401'; break; // Debit Kas, Kredit Pendapatan
            
            case 'pembelian_tunai': // Dulu 'pembelian_persediaan'
                $dr='102'; $cr='101'; break; // Debit Persediaan, Kredit Kas
            
            case 'pembelian_kredit': // FITUR BARU
                $dr='102'; $cr='201'; break; // Debit Persediaan, Kredit Utang Usaha
            
            case 'pelunasan_utang': // FITUR BARU (Bayar Utang)
                $dr='201'; $cr='101'; break; // Debit Utang Usaha, Kredit Kas

            case 'beban_ops': 
                $dr='502'; $cr='101'; break;
            case 'beban_gaji': 
                $dr='503'; $cr='101'; break;
            case 'hpp': 
                $dr='501'; $cr='102'; break;
            case 'pendapatan_lain': 
                $dr='101'; $cr='402'; break;
            case 'dividen': 
                $dr='302'; $cr='101'; break;
            case 'capex': 
                $dr='103'; $cr='101'; break;
        }

        if ($dr && $cr) {
            $drAcc = Account::where('code', $dr)->first();
            $crAcc = Account::where('code', $cr)->first();
            
            if($drAcc && $crAcc) {
                JournalEntry::create(['transaction_id' => $trx->id, 'account_id' => $drAcc->id, 'debit' => $trx->amount, 'credit' => 0]);
                JournalEntry::create(['transaction_id' => $trx->id, 'account_id' => $crAcc->id, 'debit' => 0, 'credit' => $trx->amount]);
            }
        }
    }
}