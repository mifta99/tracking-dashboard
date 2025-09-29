@extends('adminlte::page')

@section('title', 'Reported Incidents')

@section('content_header')
    <h1>Reported Incidents</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reported Incidents List</h3>

        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="reported-incidents-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Waktu Pelaporan</th>
                        <th>Insiden</th>
                        <th>Waktu Kejadian</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated here -->
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
