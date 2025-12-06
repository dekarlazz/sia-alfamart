@extends('layouts.app')

@section('content')
<style>
    @media print {
        .no-print, .navbar, .btn { display: none !important; }
        .glass-card { border: 1px solid #ccc; box-shadow: none; }
        body { background: white; color: black; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-alfa-blue mb-0">Laporan Keuangan 2024</h2>
    <button onclick="window.print()" class="btn btn-dark no-print">
        <i class="fas fa-print me-2"></i> Cetak Laporan
    </button>
</div>

<!-- Navigasi Tab -->
<ul class="nav nav-pills mb-4 gap-2 no-print" id="pills-tab" role="tablist">
    <li class="nav-item"><button class="nav-link active btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-lr">Laba Rugi</button></li>
    <li class="nav-item"><button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-ekuitas">Perubahan Ekuitas</button></li>
    <li class="nav-item"><button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-neraca">Neraca</button></li>
    <li class="nav-item"><button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-aruskas">Arus Kas</button></li>
    <li class="nav-item"><button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-analisis">Analisis Kinerja</button></li>
</ul>

<div class="tab-content">
    
    <!-- 1. LABA RUGI -->
    <div class="tab-pane fade show active" id="pills-lr">
        <div class="glass-card p-5">
            <div class="text-center mb-4">
                <h4 class="fw-bold">PT SUMBER ALFARIA TRIJAYA TBK</h4>
                <h5>LAPORAN LABA RUGI</h5>
                <small>Periode Berakhir 31 Desember 2024</small>
            </div>
            <table class="table">
                <tr><th colspan="2" class="text-success">PENDAPATAN</th></tr>
                @foreach($pendapatan as $p)
                    @php $val = $p->journalEntries->sum('credit') - $p->journalEntries->sum('debit'); @endphp
                    @if($val != 0)
                    <tr><td>{{ $p->name }}</td><td class="text-end">Rp {{ number_format($val, 0, ',', '.') }}</td></tr>
                    @endif
                @endforeach
                <tr class="fw-bold table-light"><td>Total Pendapatan</td><td class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td></tr>

                <tr><th colspan="2" class="text-danger mt-3">BEBAN</th></tr>
                @foreach($beban as $b)
                    @php $val = $b->journalEntries->sum('debit') - $b->journalEntries->sum('credit'); @endphp
                    @if($val != 0)
                    <tr><td>{{ $b->name }}</td><td class="text-end">(Rp {{ number_format($val, 0, ',', '.') }})</td></tr>
                    @endif
                @endforeach
                <tr class="fw-bold table-light"><td>Total Beban</td><td class="text-end">(Rp {{ number_format($totalBeban, 0, ',', '.') }})</td></tr>

                <tr class="table-primary fw-bold fs-5 border-top border-3 border-dark">
                    <td>LABA BERSIH</td><td class="text-end">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 2. PERUBAHAN EKUITAS -->
    <div class="tab-pane fade" id="pills-ekuitas">
        <div class="glass-card p-5">
            <div class="text-center mb-4">
                <h4 class="fw-bold">PT SUMBER ALFARIA TRIJAYA TBK</h4>
                <h5>LAPORAN PERUBAHAN EKUITAS</h5>
            </div>
            <table class="table">
                <tr><td>Modal Awal</td><td class="text-end">Rp {{ number_format($modalAwal, 0, ',', '.') }}</td></tr>
                <tr><td>(+) Laba Bersih</td><td class="text-end text-success">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td></tr>
                <tr><td>(-) Dividen</td><td class="text-end text-danger">(Rp {{ number_format($dividen, 0, ',', '.') }})</td></tr>
                <tr class="table-primary fw-bold fs-5 border-top border-3 border-dark">
                    <td>EKUITAS AKHIR</td><td class="text-end">Rp {{ number_format($ekuitasAkhir, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. NERACA -->
    <div class="tab-pane fade" id="pills-neraca">
        @if($isBalanced)
            <div class="alert alert-success text-center fw-bold mb-3 no-print"><i class="fas fa-check-circle"></i> NERACA SEIMBANG</div>
        @else
            <div class="alert alert-danger text-center fw-bold mb-3 no-print"><i class="fas fa-exclamation-triangle"></i> TIDAK SEIMBANG</div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-center border-bottom pb-2 text-primary">ASET</h5>
                    <div style="height: 200px;" class="mb-3 position-relative no-print"><canvas id="assetChart"></canvas></div>
                    <table class="table table-borderless">
                        @foreach($aset as $a)
                            @php $val = $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'); @endphp
                            <tr><td>{{ $a->name }}</td><td class="text-end">{{ number_format($val, 0, ',', '.') }}</td></tr>
                        @endforeach
                        <tr class="table-primary fw-bold fs-5 border-top border-3 border-dark"><td>TOTAL ASET</td><td class="text-end">Rp {{ number_format($totalAset, 0, ',', '.') }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-center border-bottom pb-2 text-danger">LIABILITAS & EKUITAS</h5>
                    <table class="table table-borderless">
                        <tr class="fw-bold bg-light"><td colspan="2">LIABILITAS</td></tr>
                        @foreach($liabilitas as $l)
                            @php $val = $l->journalEntries->sum('credit') - $l->journalEntries->sum('debit'); @endphp
                            <tr><td>{{ $l->name }}</td><td class="text-end">{{ number_format($val, 0, ',', '.') }}</td></tr>
                        @endforeach
                        <tr class="fw-bold border-top"><td>Total Liabilitas</td><td class="text-end">{{ number_format($totalLiabilitas, 0, ',', '.') }}</td></tr>
                        
                        <tr class="fw-bold bg-light mt-3"><td colspan="2">EKUITAS</td></tr>
                        <tr><td>Ekuitas Akhir</td><td class="text-end">{{ number_format($ekuitasAkhir, 0, ',', '.') }}</td></tr>

                        <tr class="table-primary fw-bold fs-5 border-top border-3 border-dark"><td>TOTAL LIABILITAS + EKUITAS</td><td class="text-end">Rp {{ number_format($totalPasiva, 0, ',', '.') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. ARUS KAS -->
    <div class="tab-pane fade" id="pills-aruskas">
        <div class="glass-card p-5">
            <div class="text-center mb-4">
                <h4 class="fw-bold">PT SUMBER ALFARIA TRIJAYA TBK</h4>
                <h5>LAPORAN ARUS KAS</h5>
                <small>Metode Langsung (Direct Method)</small>
            </div>
            <table class="table table-hover">
                <!-- Operasi -->
                <tr class="table-primary fw-bold"><td colspan="2">ARUS KAS DARI AKTIVITAS OPERASI</td></tr>
                <tr>
                    <td>Kas Bersih dari Operasi (Penjualan - Beban Tunai)</td>
                    <td class="text-end fw-bold {{ $kasBersihOperasi < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $kasBersihOperasi < 0 ? '(' . number_format(abs($kasBersihOperasi), 0, ',', '.') . ')' : number_format($kasBersihOperasi, 0, ',', '.') }}
                    </td>
                </tr>

                <!-- Investasi -->
                <tr class="table-warning fw-bold mt-3"><td colspan="2">ARUS KAS DARI AKTIVITAS INVESTASI</td></tr>
                <tr>
                    <td>Pembelian Aset Tetap (Capex)</td>
                    <td class="text-end fw-bold text-danger">
                        ({{ number_format(abs($kasBersihInvestasi), 0, ',', '.') }})
                    </td>
                </tr>

                <!-- Pendanaan -->
                <tr class="table-info fw-bold mt-3"><td colspan="2">ARUS KAS DARI AKTIVITAS PENDANAAN</td></tr>
                <tr>
                    <td>Kas Bersih dari Pendanaan (Utang Bank - Dividen)</td>
                    <td class="text-end fw-bold {{ $kasBersihPendanaan < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $kasBersihPendanaan < 0 ? '(' . number_format(abs($kasBersihPendanaan), 0, ',', '.') . ')' : number_format($kasBersihPendanaan, 0, ',', '.') }}
                    </td>
                </tr>

                <!-- Summary -->
                <tr class="border-top border-3 border-dark mt-4">
                    <td class="fw-bold">Kenaikan (Penurunan) Bersih Kas</td>
                    <td class="text-end fw-bold">Rp {{ number_format($kenaikanKas, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Saldo Kas Awal Tahun</td>
                    <td class="text-end">Rp {{ number_format($saldoAwalKas, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-active fw-bold fs-5">
                    <td>SALDO KAS AKHIR TAHUN</td>
                    <td class="text-end">Rp {{ number_format($saldoAkhirKas, 0, ',', '.') }}</td>
                </tr>
            </table>
            <div class="alert alert-light mt-3 border text-center small">
                *Saldo Kas Akhir Tahun harus sama dengan Saldo Kas di Neraca.
            </div>
        </div>
    </div>

    <!-- 5. ANALISIS KINERJA -->
    <div class="tab-pane fade" id="pills-analisis">
        <div class="row g-4">
            <!-- Kartu Profit Margin -->
            <div class="col-md-4">
                <div class="glass-card p-4 text-center h-100">
                    <h6 class="text-muted text-uppercase">Net Profit Margin (NPM)</h6>
                    <h2 class="fw-bold text-success display-4">{{ number_format($npm, 1) }}%</h2>
                    <p class="small text-muted">Setiap Rp 100 penjualan menghasilkan keuntungan bersih Rp {{ number_format($npm, 1) }}</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $npm }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Kartu Solvabilitas -->
            <div class="col-md-4">
                <div class="glass-card p-4 text-center h-100">
                    <h6 class="text-muted text-uppercase">Debt to Asset Ratio</h6>
                    <h2 class="fw-bold text-warning display-4">{{ number_format($dar, 1) }}%</h2>
                    <p class="small text-muted">{{ number_format($dar, 1) }}% Aset perusahaan dibiayai oleh Utang</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $dar }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Kartu ROE -->
            <div class="col-md-4">
                <div class="glass-card p-4 text-center h-100">
                    <h6 class="text-muted text-uppercase">Return on Equity (ROE)</h6>
                    <h2 class="fw-bold text-primary display-4">{{ number_format($roe, 1) }}%</h2>
                    <p class="small text-muted">Tingkat pengembalian investasi pemegang saham</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $roe }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                    <div>
                        <strong>Kesimpulan Analisis:</strong><br>
                        Perusahaan memiliki profitabilitas positif ({{ number_format($npm, 1) }}%) meskipun margin ritel cenderung tipis. 
                        Tingkat utang cukup tinggi ({{ number_format($dar, 1) }}%) yang wajar untuk industri ritel dengan perputaran persediaan cepat dan strategi utang dagang.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const ctxAsset = document.getElementById('assetChart');
    if (ctxAsset) {
        new Chart(ctxAsset, {
            type: 'doughnut',
            data: {
                labels: ['Aset Lancar', 'Aset Tetap'],
                datasets: [{
                    data: [{{ $asetLancar ?? 0 }}, {{ $asetTetap ?? 0 }}],
                    backgroundColor: ['#28a745', '#005eb8'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'bottom' } } 
            }
        });
    }
</script>
@endpush
@endsection