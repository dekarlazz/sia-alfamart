@extends('layouts.app')

@section('content')
<h2 class="fw-bold mb-4 text-alfa-blue">Buku Besar (General Ledger)</h2>

<div class="row g-4">
    @foreach($accounts as $acc)
    @php
        // Hitung Saldo Akhir
        $totalDebit = $acc->journalEntries->sum('debit');
        $totalCredit = $acc->journalEntries->sum('credit');
        
        // Rumus Saldo Normal
        if($acc->normal_balance == 'debit') {
            $saldo = $totalDebit - $totalCredit;
            $status = $saldo >= 0 ? 'text-primary' : 'text-danger';
        } else {
            $saldo = $totalCredit - $totalDebit;
            $status = $saldo >= 0 ? 'text-success' : 'text-danger';
        }
    @endphp
    
    <!-- Kartu Akun -->
    <div class="col-md-6 col-lg-4">
        <div class="glass-card p-4 h-100 position-relative hover-shadow transition-all">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge bg-secondary">{{ $acc->code }}</span>
                <span class="badge bg-light text-dark border">{{ ucfirst($acc->type) }}</span>
            </div>
            
            <h5 class="fw-bold mb-1 text-dark">{{ $acc->name }}</h5>
            <div class="d-flex justify-content-between small text-muted mb-3">
                <span>Mutasi Debit: {{ number_format($totalDebit/1000000000, 1) }} M</span>
                <span>Mutasi Kredit: {{ number_format($totalCredit/1000000000, 1) }} M</span>
            </div>

            <h3 class="fw-bold {{ $status }} mb-4">Rp {{ number_format($saldo, 0, ',', '.') }}</h3>
            
            <!-- Tombol Lihat Detail (Membuka Modal) -->
            <button type="button" class="btn btn-outline-primary w-100 rounded-pill stretched-link" data-bs-toggle="modal" data-bs-target="#modalAccount{{ $acc->id }}">
                <i class="fas fa-list-ul me-2"></i> Lihat Rincian Mutasi
            </button>
        </div>
    </div>

    <!-- MODAL DETAIL (Pop-up Rincian) -->
    <div class="modal fade" id="modalAccount{{ $acc->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content glass-card border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <span class="text-muted me-2">{{ $acc->code }}</span> {{ $acc->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($acc->journalEntries->sortBy('transaction.date') as $entry)
                                <tr>
                                    <td class="small">{{ $entry->transaction->date->format('d/m/Y') }}</td>
                                    <td class="small">{{ Str::limit($entry->transaction->description, 40) }}</td>
                                    <td class="text-end {{ $entry->debit > 0 ? 'fw-bold' : 'text-muted' }}">
                                        {{ $entry->debit > 0 ? number_format($entry->debit) : '-' }}
                                    </td>
                                    <td class="text-end {{ $entry->credit > 0 ? 'fw-bold' : 'text-muted' }}">
                                        {{ $entry->credit > 0 ? number_format($entry->credit) : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada transaksi</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2" class="text-end">Total Mutasi</td>
                                    <td class="text-end text-primary">{{ number_format($totalDebit) }}</td>
                                    <td class="text-end text-danger">{{ number_format($totalCredit) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 justify-content-between bg-light">
                    <small class="text-muted">Saldo Akhir: <strong>Rp {{ number_format($saldo) }}</strong> ({{ ucfirst($acc->normal_balance) }})</small>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        transform: translateY(-5px);
    }
    .transition-all { transition: all 0.3s ease; }
</style>
@endsection