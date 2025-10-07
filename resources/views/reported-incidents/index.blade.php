@extends('adminlte::page')

@section('title', 'Reported Incidents')

@section('content_header')
    <h1>Reported Incidents</h1>
@stop

@section('content')
    {{-- <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reported Incidents List</h3>

        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="reported-incidents-table">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="3" class="align-middle text-center" style="width:40px">NO</th>
                        <th rowspan="3" class="align-middle text-center" style="width:120px">TANGGAL KEJADIAN</th>
                        <th rowspan="3" class="align-middle text-center" style="width:150px">NAMA</th>
                        <th rowspan="3" class="align-middle text-center" style="width:120px">BAGIAN</th>
                        <th rowspan="3" class="align-middle text-center" style="width:150px">KRONOLOGIS KEJADIAN</th>
                        <th colspan="6" class="text-center">TINDAKAN KOREKSI</th>
                        <th colspan="6" class="text-center">TINDAKAN KOREKTIF</th>
                    </tr>
                    <tr>
                        <!-- Tindakan Koreksi columns -->
                        <th rowspan="2" class="align-middle text-center" style="width:120px">RENCANA TINDAKAN KOREKSI
                        </th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">PELAKSANA TINDAKAN</th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">TANGGAL SELESAI</th>
                        <th colspan="3" class="text-center">VERIFIKASI</th>
                        <!-- Tindakan Korektif columns -->
                        <th rowspan="2" class="align-middle text-center" style="width:120px">RENCANA TINDAKAN KOREKTIF
                        </th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">PELAKSANA TINDAKAN</th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">TANGGAL SELESAI</th>
                        <th colspan="3" class="text-center">VERIFIKASI</th>
                    </tr>
                    <tr>
                        <!-- Verifikasi sub-columns for Tindakan Koreksi -->
                        <th class="text-center" style="width:80px">HASIL</th>
                        <th class="text-center" style="width:100px">TANGGAL</th>
                        <th class="text-center" style="width:100px">PELAKSANA</th>
                        <!-- Verifikasi sub-columns for Tindakan Korektif -->
                        <th class="text-center" style="width:80px">HASIL</th>
                        <th class="text-center" style="width:100px">TANGGAL</th>
                        <th class="text-center" style="width:100px">PELAKSANA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="17" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle"></i> No incidents reported yet
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div> --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="h5 mb-0">Start of Incident</span>
            <a href="" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> New Incident
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0" id="reported-incidents-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:160px">Tanggal</th>
                        <th class="text-center">Kategori Insiden</th>
                        <th class="text-center" style="width:140px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $items = isset($reportedIncidents) ? $reportedIncidents : (isset($incidents) ? $incidents : collect());
                    @endphp
                    @forelse($items as $incident)
                        @php
                            $date = $incident->tanggal_kejadian ?? $incident->date ?? null;
                            $category = $incident->kategori_insiden ?? $incident->category ?? '-';
                            $status = $incident->status ?? 'unknown';
                            $badgeClass = [
                                'open' => 'danger',
                                'in_progress' => 'warning',
                                'closed' => 'success',
                            ][$status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="text-center">{{ $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $category }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ \Illuminate\Support\Str::of($status)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                <i class="fas fa-info-circle"></i> No incidents reported yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        console.log('Reported Incidents page loaded');
    </script>
@stop
