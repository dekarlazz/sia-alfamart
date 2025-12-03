@extends('layouts.app')

@section('content')
<h2 class="fw-bold mb-4 text-alfa-blue">Buku Besar</h2>

<div class="row g-4">
    @foreach($accounts as $acc)
    @php
        // Hitung Saldo Akhir
        $saldo = $acc->normal_balance == 'debit' 
            ? $acc->journalEntries->sum('debit') - $acc->journalEntries->sum('credit')
            : $acc->journalEntries->sum('credit') - $acc->journalEntries->sum('debit');
    @endphp
    <div class="col-md-6 col-lg-4">
        <div class="glass-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-secondary">{{ $acc->code }}</span>
                <small class="text-muted">{{ strtoupper($acc->normal_balance) }}</small>
            </div>
            <h6 class="fw-bold mb-0">{{ $acc->name }}</h6>
            <h3 class="fw-bold text-alfa-red my-3">Rp {{ number_format($saldo, 0, ',', '.') }}</h3>
        </div>
    </div>
    @endforeach
</div>
@endsection