@extends('layouts.app')

@section('content')
<h2 class="fw-bold mb-4 text-alfa-blue">Laporan Keuangan</h2>

<ul class="nav nav-pills mb-4 gap-2" id="pills-tab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-lr">Laba Rugi</button>
    </li>
    <li class="nav-item">
        <button class="nav-link btn-alfa-red" data-bs-toggle="pill" data-bs-target="#pills-neraca">Neraca</button>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab Laba Rugi -->
    <div class="tab-pane fade show active" id="pills-lr">
        <div class="glass-card p-5">
            <h4 class="text-center fw-bold mb-4">Laporan Laba Rugi</h4>
            <table class="table">
                <tr class="table-light fw-bold">
                    <td>Total Pendapatan</td>
                    <td class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-light fw-bold">
                    <td>Total Beban</td>
                    <td class="text-end text-danger">(Rp {{ number_format($totalBeban, 0, ',', '.') }})</td>
                </tr>
                <tr class="table-primary fw-bold fs-5">
                    <td>LABA BERSIH</td>
                    <td class="text-end">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Tab Neraca -->
    <div class="tab-pane fade" id="pills-neraca">
        <div class="row">
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-center border-bottom pb-2">ASET</h5>
                    <table class="table table-borderless">
                        <tr><td>Total Aset</td><td class="text-end fw-bold">Rp {{ number_format($totalAset, 0, ',', '.') }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-center border-bottom pb-2">LIABILITAS & EKUITAS</h5>
                    <table class="table table-borderless">
                        <tr><td>Total Liabilitas</td><td class="text-end fw-bold">{{ number_format($totalLiabilitas, 0, ',', '.') }}</td></tr>
                        <tr><td>Total Ekuitas</td><td class="text-end fw-bold">{{ number_format($ekuitasAkhir, 0, ',', '.') }}</td></tr>
                        <tr class="border-top fw-bold fs-5">
                            <td>TOTAL L & E</td>
                            <td class="text-end">Rp {{ number_format($totalLiabilitas + $ekuitasAkhir, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection