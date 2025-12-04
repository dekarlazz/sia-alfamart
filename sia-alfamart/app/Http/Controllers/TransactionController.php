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
        $transactions = Transaction::orderBy('date', 'desc')->get();
        
        // 1. Hitung Saldo Kas (Optimized)
        $kasAcc = Account::where('code', '101')->first();
        $totalKas = 0;
        if ($kasAcc) {
            $totalKas = JournalEntry::where('account_id', $kasAcc->id)
                ->sum(DB::raw('debit - credit'));
        }

        // 2. Ringkasan Atas
        $pendapatan = JournalEntry::whereHas('account', fn($q) => $q->where('type', 'penghasilan'))->sum('credit');
        $beban = JournalEntry::whereHas('account', fn($q) => $q->where('type', 'beban'))->sum('debit');

        // 3. Grafik 1: Tren Bulanan (Income vs Expense) - SQLite Syntax
        $monthlyPerformance = DB::table('transactions')
            ->selectRaw("strftime('%Y-%m', date) as month, 
                         SUM(CASE WHEN type IN ('penjualan', 'pendapatan_lain') THEN amount ELSE 0 END) as income,
                         SUM(CASE WHEN type IN ('beban_ops', 'beban_gaji', 'hpp', 'beban_pajak') THEN amount ELSE 0 END) as expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 4. Grafik 2: Komposisi Beban (Pie Chart)
        $expenseBreakdown = DB::table('transactions')
            ->whereIn('type', ['hpp', 'beban_ops', 'beban_gaji', 'beban_pajak'])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        return view('transactions.index', [
            'transactions' => $transactions,
            'totalKas' => $totalKas,
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            // Data Grafik Tren Bulanan
            'months' => $monthlyPerformance->pluck('month'),
            'incomes' => $monthlyPerformance->pluck('income'),
            'expenses' => $monthlyPerformance->pluck('expense'),
            // Data Grafik Pie
            'expenseTypes' => $expenseBreakdown->pluck('type'),
            'expenseTotals' => $expenseBreakdown->pluck('total')
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