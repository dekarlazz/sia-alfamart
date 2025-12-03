<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\JournalEntry;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Bagan Akun (COA)
        $accounts = [
            ['code' => '101', 'name' => 'Kas & Setara Kas', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '102', 'name' => 'Persediaan', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '103', 'name' => 'Aset Tetap & Lainnya', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '201', 'name' => 'Liabilitas', 'type' => 'liabilitas', 'normal_balance' => 'kredit'],
            ['code' => '301', 'name' => 'Modal Saham', 'type' => 'ekuitas', 'normal_balance' => 'kredit'],
            ['code' => '303', 'name' => 'Dividen', 'type' => 'deviden', 'normal_balance' => 'debit'],
            ['code' => '401', 'name' => 'Pendapatan Usaha', 'type' => 'penghasilan', 'normal_balance' => 'kredit'],
            ['code' => '402', 'name' => 'Pendapatan Lain', 'type' => 'penghasilan', 'normal_balance' => 'kredit'],
            ['code' => '501', 'name' => 'Beban Pokok (HPP)', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '502', 'name' => 'Beban Operasional', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '503', 'name' => 'Beban Gaji', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '504', 'name' => 'Beban Pajak', 'type' => 'beban', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $acc) {
            Account::create($acc);
        }

        // Helper biar gampang cari ID akun
        $getAcc = fn($code) => Account::where('code', $code)->first()->id;
        $multiplier = 1000000; // Konversi Juta ke Rupiah Penuh

        // 2. Saldo Awal 2023
        $saldoAwal = Transaction::create([
            'date' => Carbon::parse('2023-12-31'),
            'description' => 'Saldo Awal 2023',
            'type' => 'saldo_awal',
            'amount' => 0
        ]);

        $entries = [
            ['acc' => '101', 'debit' => 4074530 * $multiplier, 'credit' => 0],
            ['acc' => '102', 'debit' => 10094023 * $multiplier, 'credit' => 0],
            ['acc' => '103', 'debit' => 20077630 * $multiplier, 'credit' => 0],
            ['acc' => '201', 'debit' => 0, 'credit' => 18540983 * $multiplier],
            ['acc' => '301', 'debit' => 0, 'credit' => 15705200 * $multiplier],
        ];

        foreach ($entries as $e) {
            JournalEntry::create([
                'transaction_id' => $saldoAwal->id,
                'account_id' => $getAcc($e['acc']),
                'debit' => $e['debit'],
                'credit' => $e['credit']
            ]);
        }

        // 3. Transaksi 2024
        $transaksi = [
            ['2024-01-15', 'Pendapatan Penjualan', 'penjualan', 118227031 * $multiplier],
            ['2024-02-20', 'Beban Pokok (HPP)', 'hpp', 92861550 * $multiplier],
            ['2024-03-10', 'Beli Persediaan', 'pembelian_persediaan', 94542349 * $multiplier],
            ['2024-04-05', 'Beban Operasional', 'beban_ops', 20206085 * $multiplier],
            ['2024-05-12', 'Gaji Karyawan', 'beban_gaji', 2177500 * $multiplier],
            ['2024-06-20', 'Pendapatan Bunga', 'pendapatan_lain', 108979 * $multiplier],
            ['2024-07-15', 'Beban Pajak', 'beban_pajak', 919970 * $multiplier],
            ['2024-08-01', 'Dividen Tunai', 'dividen', 1190930 * $multiplier],
            ['2024-09-10', 'Belanja Modal (Capex)', 'capex', 5056075 * $multiplier],
        ];

        foreach ($transaksi as $trx) {
            $t = Transaction::create([
                'date' => $trx[0], 'description' => $trx[1], 'type' => $trx[2], 'amount' => $trx[3]
            ]);

            // Logic Jurnal Sederhana
            $dr = ''; $cr = '';
            switch ($trx[2]) {
                case 'penjualan': $dr='101'; $cr='401'; break;
                case 'hpp': $dr='501'; $cr='102'; break;
                case 'pembelian_persediaan': $dr='102'; $cr='101'; break;
                case 'beban_ops': $dr='502'; $cr='101'; break;
                case 'beban_gaji': $dr='503'; $cr='101'; break;
                case 'pendapatan_lain': $dr='101'; $cr='402'; break;
                case 'beban_pajak': $dr='504'; $cr='101'; break;
                case 'dividen': $dr='303'; $cr='101'; break;
                case 'capex': $dr='103'; $cr='101'; break;
            }

            if($dr && $cr) {
                JournalEntry::create(['transaction_id' => $t->id, 'account_id' => $getAcc($dr), 'debit' => $trx[3], 'credit' => 0]);
                JournalEntry::create(['transaction_id' => $t->id, 'account_id' => $getAcc($cr), 'debit' => 0, 'credit' => $trx[3]]);
            }
        }
    }
}