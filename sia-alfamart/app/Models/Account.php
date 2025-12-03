<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    // Ini biar kita bisa isi data langsung banyak (mass assignment)
    protected $guarded = [];

    // Satu akun bisa punya banyak jurnal
    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}