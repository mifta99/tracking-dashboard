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
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        console.log('Reported Incidents page loaded');
    </script>
@stop
