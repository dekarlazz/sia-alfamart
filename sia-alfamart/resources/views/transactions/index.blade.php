@extends('layouts.app')

@section('content')
<!-- Widget Ringkasan -->
<div class="row g-4 mb-4">
    <!-- Kas -->
    <div class="col-md-4">
        <div class="glass-card p-4 h-100 border-start border-5 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Total Kas & Bank</h6>
                    <h3 class="fw-bold text-alfa-blue">Rp {{ number_format($totalKas, 0, ',', '.') }}</h3>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Liquid Assets</span>
                </div>
                <i class="fas fa-wallet fa-3x text-alfa-blue opacity-25"></i>
            </div>
        </div>
    </div>
    <!-- Pendapatan -->
    <div class="col-md-4">
        <div class="glass-card p-4 h-100 border-start border-5 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Total Pendapatan</h6>
                    <h3 class="fw-bold text-success">Rp {{ number_format($pendapatan, 0, ',', '.') }}</h3>
                    <span class="badge bg-success bg-opacity-10 text-success">+ Growth</span>
                </div>
                <i class="fas fa-chart-line fa-3x text-success opacity-25"></i>
            </div>
        </div>
    </div>
    <!-- Beban -->
    <div class="col-md-4">
        <div class="glass-card p-4 h-100 border-start border-5 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted text-uppercase mb-2">Total Beban</h6>
                    <h3 class="fw-bold text-danger">Rp {{ number_format($beban, 0, ',', '.') }}</h3>
                    <span class="badge bg-danger bg-opacity-10 text-danger">High Cost</span>
                </div>
                <i class="fas fa-file-invoice-dollar fa-3x text-danger opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- Area Grafik -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4 text-alfa-blue"><i class="fas fa-chart-bar me-2"></i>Tren Kinerja Bulanan</h5>
            <canvas id="trendChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4 text-danger"><i class="fas fa-chart-pie me-2"></i>Komposisi Beban</h5>
            <div style="height: 250px; position: relative;">
                <canvas id="expenseChart"></canvas>
            </div>
            <div class="mt-3 text-center small text-muted">
                Proporsi HPP vs Operasional
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Form Input -->
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
                        <option value="" disabled selected>-- Pilih Jenis --</option>
                        <optgroup label="Pemasukan">
                            <option value="penjualan">Penjualan (Uang Masuk)</option>
                            <option value="pendapatan_lain">Pendapatan Lain</option>
                        </optgroup>
                        <optgroup label="Pengeluaran Tunai">
                            <option value="pembelian_tunai">Beli Persediaan (Tunai)</option>
                            <option value="beban_ops">Bayar Listrik/Air/Sewa</option>
                            <option value="beban_gaji">Bayar Gaji</option>
                            <option value="beban_pajak">Bayar Pajak</option>
                            <option value="dividen">Bayar Dividen</option>
                            <option value="capex">Beli Aset (Capex)</option>
                            <option value="pelunasan_utang">Pelunasan Utang Usaha</option>
                        </optgroup>
                        <optgroup label="Non-Tunai / Kredit">
                            <option value="pembelian_kredit">Beli Persediaan (Utang)</option>
                            <option value="hpp">Pencatatan HPP</option>
                        </optgroup>
                        <optgroup label="Pendanaan">
                            <option value="utang_bank">Pencairan Pinjaman Bank</option>
                        </optgroup>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Keterangan</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Setoran Toko" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small text-muted">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control fw-bold" placeholder="0" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-alfa-red w-100 py-2">Simpan Transaksi</button>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="col-lg-8">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-3">Riwayat Transaksi Terakhir</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
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
                            <td>{{ Str::limit($t->description, 25) }}</td>
                            <td>
                                @php
                                    $badge = match($t->type) {
                                        'penjualan', 'pendapatan_lain', 'utang_bank' => 'success',
                                        'pembelian_kredit', 'hpp' => 'warning',
                                        default => 'danger'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }} bg-opacity-10 text-{{ $badge }} border border-{{ $badge }}">
                                    {{ $t->type }}
                                </span>
                            </td>
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
    // 1. Grafik Tren Bulanan (Bar Chart)
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'Pendapatan',
                data: {!! json_encode($incomes) !!},
                backgroundColor: '#005eb8',
                borderRadius: 5
            }, {
                label: 'Beban',
                data: {!! json_encode($expenses) !!},
                backgroundColor: '#d7111b',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, grid: { display: false } } }
        }
    });

    // 2. Grafik Komposisi Beban (Doughnut Chart)
    const expenseLabels = {!! json_encode($expenseTypes) !!}.map(t => t.toUpperCase().replace('_', ' '));
    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
        data: {
            labels: expenseLabels,
            datasets: [{
                data: {!! json_encode($expenseTotals) !!},
                backgroundColor: ['#ffcd56', '#ff6384', '#36a2eb', '#fd7e14', '#4bc0c0'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } 
            }
        }
    });
</script>
@endpush
@endsection