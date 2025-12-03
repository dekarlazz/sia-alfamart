<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Accounts (Daftar Akun: Kas, HPP, dll)
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode akun: 101, 401
            $table->string('name');
            $table->enum('type', ['aset', 'liabilitas', 'ekuitas', 'penghasilan', 'beban', 'deviden']);
            $table->enum('normal_balance', ['debit', 'kredit']);
            $table->timestamps();
        });

        // 2. Tabel Transactions (Nota/Header Transaksi)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('description');
            $table->string('type'); // Jenis: penjualan, beban_ops, dll
            // decimal(20, 2) biar muat angka Triliunan Alfamart
            $table->decimal('amount', 20, 2); 
            $table->timestamps();
        });

        // 3. Tabel Journal Entries (Rincian Debit/Kredit)
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            // Penghubung ke tabel transaksi (kalau transaksi dihapus, ini ikut hilang)
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            // Penghubung ke tabel akun
            $table->foreignId('account_id')->constrained('accounts');
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('accounts');
    }
};