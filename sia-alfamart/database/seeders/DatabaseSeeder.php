<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('DELETE FROM journal_entries');
        DB::statement('DELETE FROM transactions');
        DB::statement('DELETE FROM accounts');

        $accounts = [
            ['code' => '101', 'name' => 'Kas & Setara Kas', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '102', 'name' => 'Persediaan Barang', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '103', 'name' => 'Aset Tetap', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '201', 'name' => 'Utang Usaha', 'type' => 'liabilitas', 'normal_balance' => 'kredit'],
            ['code' => '202', 'name' => 'Utang Lain-lain', 'type' => 'liabilitas', 'normal_balance' => 'kredit'],
            ['code' => '301', 'name' => 'Modal Saham', 'type' => 'ekuitas', 'normal_balance' => 'kredit'],
            ['code' => '302', 'name' => 'Dividen', 'type' => 'deviden', 'normal_balance' => 'debit'],
            ['code' => '401', 'name' => 'Pendapatan Penjualan', 'type' => 'penghasilan', 'normal_balance' => 'kredit'],
            ['code' => '402', 'name' => 'Pendapatan Lainnya', 'type' => 'penghasilan', 'normal_balance' => 'kredit'],
            ['code' => '501', 'name' => 'Beban Pokok (HPP)', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '502', 'name' => 'Beban Operasional', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '503', 'name' => 'Beban Gaji', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '504', 'name' => 'Beban Pajak', 'type' => 'beban', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $acc) {
            Account::create($acc);
        }

        $getAcc = fn($code) => Account::where('code', $code)->first()->id;
        $m = 1000000;

        // SALDO AWAL
        $saldoAwal = Transaction::create(['date' => '2023-12-31', 'description' => 'Saldo Awal 2024', 'type' => 'saldo_awal', 'amount' => 0]);
        
        $entries = [
            ['acc' => '101', 'debit' => 4074530 * $m, 'credit' => 0],
            ['acc' => '102', 'debit' => 10094023 * $m, 'credit' => 0],
            ['acc' => '103', 'debit' => 20077630 * $m, 'credit' => 0],
            ['acc' => '201', 'debit' => 0, 'credit' => 10865742 * $m],
            ['acc' => '202', 'debit' => 0, 'credit' => 7675241 * $m],
            ['acc' => '301', 'debit' => 0, 'credit' => 15705200 * $m],
        ];

        foreach ($entries as $e) {
            JournalEntry::create(['transaction_id' => $saldoAwal->id, 'account_id' => $getAcc($e['acc']), 'debit' => $e['debit'], 'credit' => $e['credit']]);
        }

        // TRANSAKSI 2024
        $transaksi = [
            ['2024-01-15', 'Pendapatan Penjualan Q1', 'penjualan', 29000000 * $m],
            ['2024-04-15', 'Pendapatan Penjualan Q2', 'penjualan', 30000000 * $m],
            ['2024-07-15', 'Pendapatan Penjualan Q3', 'penjualan', 29000000 * $m],
            ['2024-10-15', 'Pendapatan Penjualan Q4', 'penjualan', 30227031 * $m],
            
            ['2024-02-10', 'Pembelian Persediaan (Tunai)', 'pembelian_tunai', 20000000 * $m],
            ['2024-05-10', 'Pembelian Persediaan (Tunai)', 'pembelian_tunai', 20000000 * $m],
            ['2024-08-10', 'Pembelian Persediaan (Tunai)', 'pembelian_tunai', 20000000 * $m],
            ['2024-11-10', 'Pembelian Persediaan (Tunai)', 'pembelian_tunai', 21542349 * $m],
            ['2024-12-01', 'Pembelian Persediaan (Kredit/Tempo)', 'pembelian_kredit', 13000000 * $m],

            ['2024-12-31', 'Pencatatan HPP', 'hpp', 92861550 * $m],
            ['2024-06-20', 'Beban Penjualan & Distribusi', 'beban_ops', 20206085 * $m],
            ['2024-12-25', 'Beban Umum & Admin', 'beban_gaji', 2177500 * $m],
            ['2024-12-15', 'Pendapatan Lain-lain', 'pendapatan_lain', 1223612 * $m],
            ['2024-06-12', 'Pembayaran Dividen', 'dividen', 1190930 * $m],
            ['2024-12-30', 'Pembayaran Pajak', 'beban_pajak', 792102 * $m],
            ['2024-09-10', 'Belanja Modal', 'capex', 5056075 * $m],
        ];

        foreach ($transaksi as $trx) {
            $t = Transaction::create(['date' => $trx[0], 'description' => $trx[1], 'type' => $trx[2], 'amount' => $trx[3]]);
            
            $dr = ''; $cr = '';
            switch ($trx[2]) {
                case 'penjualan': $dr='101'; $cr='401'; break;
                case 'pembelian_tunai': $dr='102'; $cr='101'; break;
                case 'pembelian_kredit': $dr='102'; $cr='201'; break; // KAS AMAN
                case 'hpp': $dr='501'; $cr='102'; break;
                case 'beban_ops': $dr='502'; $cr='101'; break;
                case 'beban_gaji': $dr='503'; $cr='101'; break;
                case 'pendapatan_lain': $dr='101'; $cr='402'; break;
                case 'beban_pajak': $dr='504'; $cr='101'; break;
                case 'dividen': $dr='302'; $cr='101'; break;
                case 'capex': $dr='103'; $cr='101'; break;
            }

            if($dr && $cr) {
                JournalEntry::create(['transaction_id' => $t->id, 'account_id' => $getAcc($dr), 'debit' => $trx[3], 'credit' => 0]);
                JournalEntry::create(['transaction_id' => $t->id, 'account_id' => $getAcc($cr), 'debit' => 0, 'credit' => $trx[3]]);
            }
        }
    }
}