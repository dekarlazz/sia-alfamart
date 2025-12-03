@extends('layouts.app')

@section('content')
<h2 class="fw-bold mb-4 text-alfa-blue">Jurnal Umum</h2>
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Akun / Keterangan</th>
                    <th>Ref</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry->transaction->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="fw-bold text-dark">{{ $entry->account->name }}</span><br>
                        <small class="text-muted fst-italic">{{ $entry->transaction->description }}</small>
                    </td>
                    <td>{{ $entry->account->code }}</td>
                    <td class="text-end">{{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-end">{{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', '.') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection