<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $guarded = [];

    // Pastikan tanggal dibaca sebagai Tanggal, dan angka sebagai desimal
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}