<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2'
    ];

    // Jurnal ini milik transaksi mana?
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Jurnal ini pakai akun apa?
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}