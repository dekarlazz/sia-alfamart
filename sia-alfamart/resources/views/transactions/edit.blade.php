@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="glass-card p-5">
            <h4 class="fw-bold mb-4 text-alfa-blue">Edit Transaksi</h4>
            <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $transaction->date->format('Y-m-d') }}" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Jenis Transaksi</label>
                    <select name="type" class="form-select" required>
                        @foreach(['penjualan', 'pembelian_persediaan', 'beban_ops', 'beban_gaji', 'hpp', 'pendapatan_lain', 'dividen', 'capex'] as $type)
                            <option value="{{ $type }}" {{ $transaction->type == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="description" class="form-control" value="{{ $transaction->description }}" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control fw-bold" value="{{ $transaction->amount }}" step="0.01" required>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary w-50">Batal</a>
                    <button type="submit" class="btn btn-alfa-red w-50">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection