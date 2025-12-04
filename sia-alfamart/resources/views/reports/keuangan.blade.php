@extends('layouts.app')

@section('content')
<h2 class="fw-bold mb-4 text-alfa-blue">Laporan Keuangan</h2>

<!-- Navigasi Tab -->
<ul class="nav nav-pills mb-4 gap-2" id="pills-tab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-lr">Laba Rugi</button>
    </li>
    <li class="nav-item">
        <button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-ekuitas">Perubahan Ekuitas</button>
    </li>
    <li class="nav-item">
        <button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-neraca">Neraca (Posisi Keuangan)</button>
    </li>
</ul>

<div class="tab-content">
    
    <!-- 1. TAB LABA RUGI -->
    <div class="tab-pane fade show active" id="pills-lr">
        <div class="glass-card p-5">
            <h4 class="text-center fw-bold mb-4">Laporan Laba Rugi</h4>
            <table class="table">
                <!-- Pendapatan -->
                <tr><th colspan="2" class="text-success">PENDAPATAN</th></tr>
                @foreach($pendapatan as $p)
                    @php $val = $p->journalEntries->sum('credit') - $p->journalEntries->sum('debit'); @endphp
                    @if($val != 0)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td class="text-end">Rp {{ number_format($val, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr class="fw-bold table-light">
                    <td>Total Pendapatan</td>
                    <td class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>

                <!-- Beban -->
                <tr><th colspan="2" class="text-danger mt-3">BEBAN</th></tr>
                @foreach($beban as $b)
                    @php $val = $b->journalEntries->sum('debit') - $b->journalEntries->sum('credit'); @endphp
                    @if($val != 0)
                    <tr>
                        <td>{{ $b->name }}</td>
                        <td class="text-end">(Rp {{ number_format($val, 0, ',', '.') }})</td>
                    </tr>
                    @endif
                @endforeach
                <tr class="fw-bold table-light">
                    <td>Total Beban</td>
                    <td class="text-end">(Rp {{ number_format($totalBeban, 0, ',', '.') }})</td>
                </tr>

                <!-- Hasil Akhir -->
                <tr class="table-primary fw-bold fs-5 border-top border-3 border-dark">
                    <td>LABA BERSIH TAHUN BERJALAN</td>
                    <td class="text-end">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 2. TAB PERUBAHAN EKUITAS -->
    <div class="tab-pane fade" id="pills-ekuitas">
        <div class="glass-card p-5">
            <h4 class="text-center fw-bold mb-4">Laporan Perubahan Ekuitas</h4>
            <table class="table">
                <tr>
                    <td>Modal Awal (Saldo Awal)</td>
                    <td class="text-end">Rp {{ number_format($modalAwal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Ditambah: Laba Bersih Tahun Berjalan</td>
                    <td class="text-end text-success">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dikurangi: Dividen (Prive)</td>
                    <td class="text-end text-danger">(Rp {{ number_format($dividen, 0, ',', '.') }})</td>
                </tr>
                <tr class="table-primary fw-bold fs-5 border-top border-3 border-dark">
                    <td>EKUITAS AKHIR (Modal Akhir)</td>
                    <td class="text-end">Rp {{ number_format($ekuitasAkhir, 0, ',', '.') }}</td>
                </tr>
            </table>
            <small class="text-muted">*Angka Ekuitas Akhir ini yang akan masuk ke Neraca.</small>
        </div>
    </div>

    <!-- 3. TAB NERACA -->
    <div class="tab-pane fade" id="pills-neraca">
        
        <!-- Indikator Balance -->
        @if($isBalanced)
            <div class="alert alert-success text-center fw-bold mb-3">
                <i class="fas fa-check-circle"></i> NERACA SEIMBANG (BALANCE)
            </div>
        @else
            <div class="alert alert-danger text-center fw-bold mb-3">
                <i class="fas fa-exclamation-triangle"></i> TIDAK SEIMBANG (Selisih: Rp {{ number_format($totalAset - $totalPasiva, 0, ',', '.') }})
            </div>
        @endif

        <div class="row">
            <!-- Sisi Aset (Kiri) -->
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-center border-bottom pb-2 text-primary">ASET</h5>
                    <table class="table table-borderless">
                        @foreach($aset as $a)
                            @php $val = $a->journalEntries->sum('debit') - $a->journalEntries->sum('credit'); @endphp
                            <tr>
                                <td>{{ $a->name }}</td>
                                <td class="text-end">{{ number_format($val, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-top border-2 fw-bold fs-5">
                            <td>TOTAL ASET</td>
                            <td class="text-end">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Sisi Pasiva (Kanan) -->
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-center border-bottom pb-2 text-danger">LIABILITAS & EKUITAS</h5>
                    <table class="table table-borderless">
                        <!-- Liabilitas -->
                        <tr class="fw-bold text-muted bg-light">
                            <td colspan="2">LIABILITAS (KEWAJIBAN)</td>
                        </tr>
                        @foreach($liabilitas as $l)
                            @php $val = $l->journalEntries->sum('credit') - $l->journalEntries->sum('debit'); @endphp
                            <tr>
                                <td>{{ $l->name }}</td>
                                <td class="text-end">{{ number_format($val, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold border-top">
                            <td>Total Liabilitas</td>
                            <td class="text-end">{{ number_format($totalLiabilitas, 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Ekuitas -->
                        <tr class="fw-bold text-muted bg-light mt-3">
                            <td colspan="2">EKUITAS (MODAL)</td>
                        </tr>
                        <tr>
                            <td>Ekuitas Akhir (Lihat Tab Perubahan Ekuitas)</td>
                            <td class="text-end">{{ number_format($ekuitasAkhir, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="border-top border-2 fw-bold fs-5">
                            <td>TOTAL LIABILITAS + EKUITAS</td>
                            <td class="text-end">Rp {{ number_format($totalPasiva, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection