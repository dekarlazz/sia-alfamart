@extends('layouts.app')

@section('content')
<!-- Widget Ringkasan -->
<div class="row g-4 mb-4">
    <!-- Kas -->
    <div class="col-md-4">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Total Kas</h6>
                    <h3 class="fw-bold text-alfa-blue">Rp {{ number_format($totalKas, 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-wallet fa-3x text-alfa-blue opacity-25"></i>
            </div>
        </div>
    </div>
    <!-- Pendapatan -->
    <div class="col-md-4">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Total Pendapatan</h6>
                    <h3 class="fw-bold text-success">Rp {{ number_format($pendapatan, 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-chart-line fa-3x text-success opacity-25"></i>
            </div>
        </div>
    </div>
    <!-- Beban -->
    <div class="col-md-4">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Total Beban</h6>
                    <h3 class="fw-bold text-danger">Rp {{ number_format($beban, 0, ',', '.') }}</h3>
                </div>
                <i class="fas fa-coins fa-3x text-danger opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Form Input (Kiri) -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle text-alfa-red"></i> Input Transaksi</h5>
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small text-muted">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Jenis Transaksi</label>
                    <select name="type" class="form-select" required>
                        <option value="penjualan">Penjualan (Uang Masuk)</option>
                        <option value="pembelian_tunai">Beli Persediaan (Tunai)</option>
                        <option value="pembelian_kredit">Beli Persediaan (Kredit/Utang)</option>
                        <option value="pelunasan_utang">Bayar Utang Usaha</option>
                        <option value="beban_ops">Bayar Listrik/Air</option>
                        <option value="beban_gaji">Bayar Gaji</option>
                        <option value="hpp">Catat HPP (Stok Berkurang)</option>
                        <option value="pendapatan_lain">Pendapatan Lain</option>
                        <option value="dividen">Bayar Dividen</option>
                        <option value="capex">Beli Aset (Capex)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Keterangan</label>
                    <input type="text" name="description" class="form-control" placeholder="Cth: Setoran Toko" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small text-muted">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control fw-bold" placeholder="0" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-alfa-red w-100 py-2">Simpan Transaksi</button>
            </form>
        </div>
    </div>

    <!-- Grafik & Tabel (Kanan) -->
    <div class="col-lg-8">
        <!-- Grafik -->
        <div class="glass-card p-4 mb-4">
            <h5 class="fw-bold mb-3">Arus Kas Harian</h5>
            <canvas id="cashFlowChart" height="100"></canvas>
        </div>

        <!-- Tabel Riwayat -->
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3">Riwayat Transaksi Terakhir</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tgl</th>
                            <th>Ket</th>
                            <th>Tipe</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions->take(5) as $t)
                        <tr>
                            <td>{{ $t->date->format('d/m') }}</td>
                            <td>{{ Str::limit($t->description, 20) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $t->type }}</span></td>
                            <td class="text-end fw-bold">Rp {{ number_format($t->amount, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('transactions.edit', $t->id) }}" class="btn btn-sm btn-outline-primary border-0"><i class="fas fa-pen"></i></a>
                                <form action="{{ route('transactions.destroy', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const ctx = document.getElementById('cashFlowChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Masuk',
                data: {!! json_encode($chartMasuk) !!},
                borderColor: '#198754', tension: 0.4, fill: false
            }, {
                label: 'Keluar',
                data: {!! json_encode($chartKeluar) !!},
                borderColor: '#dc3545', tension: 0.4, fill: false
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } } }
    });
</script>
@endpush
@endsection