@extends('adminlte::page')

@section('title', 'Raised Issues')

@section('content_header')
    <h1>Raised Issues</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Issues List</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Issue
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="issues-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created At</th>
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
        console.log('Raised Issues page loaded');
    </script>
@stop