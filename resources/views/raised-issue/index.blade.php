@extends('adminlte::page')

@section('title', 'Raised Issues')

@section('content_header')
    <h1>Raised Issues</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header text-md font-weight-bold" style="background-color: #ce8220; color: white;">
            <h3 class="card-title">Data Keluhan</h3>
            @if(auth()->user() && auth()->user()->role->name === 'puskesmas')
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Keluhan Baru
                </button>
            </div>
            @endif
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-sm text-sm" id="issues-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Puskesmas</th>
                        <th>Tanggal Keluhan</th>
                        <th>PIC Puskesmas</th>
                        <th>Keluhan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $issue)
                    <tr>
                        <td>{{ $issue->id }}</td>
                        <td>{{ $issue->reporter->puskesmas->name ?? 'N/A' }}</td>
                        <td>{{ $issue->created_at->format('d-m-Y') }}</td>
                        <td>{{ $issue->reporter->puskesmas->kepala  ?? 'N/A' }}</td>
                        <td>{{ $issue->reported_issue }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                switch($issue->status_id) {
                                    case 1:
                                        $statusClass = 'badge-danger';
                                        break;
                                    case 2:
                                        $statusClass = 'badge-warning';
                                        break;
                                    case 3:
                                        $statusClass = 'badge-success';
                                        break;
                                    default:
                                        $statusClass = 'badge-secondary';
                                }
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $issue->statusKeluhan->status ?? 'N/A')) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="" class="text-center">
                                <i class="fas fa-search"></i> 
                            </a>
                    </tr>
                    @endforeach
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
        console.log('Raised Issues page loaded');
    </script>
@stop