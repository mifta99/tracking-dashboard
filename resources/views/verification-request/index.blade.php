@extends('adminlte::page')

@section('title', 'Verification Requests')

@section('content_header')
    <h1>Verification Requests</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Verification Requests</h3>
            <div class="card-tools">
                {{-- <a href="{{ route('verification-request.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Request
                </a> --}}
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="verification-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach($verificationRequests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>{{ $request->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $request->status === 'approved' ? 'success' : ($request->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            <a href="{{ route('verification-request.show', $request->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('verification-request.edit', $request->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach --}}
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
        $(document).ready(function() {
            $('#verification-table').DataTable();
        });
    </script>
@stop