@extends('adminlte::page')

@section('title', 'Import Data')

@section('content_header')
    <h1 style="font-size: 24px;">Import Data</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-2">
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded" role="alert">
                <div class="d-flex align-items-center justify-content-between">
                    
                    <!-- Bagian kiri: icon + teks -->
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-excel fa-2x mr-3 text-white"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Format Template Required</h6>
                            <small class="text-white-50">
                                Pastikan file <strong>XLSX</strong> mengikuti format template yang disediakan 
                                agar import data berjalan dengan benar.
                            </small>
                        </div>
                    </div>

                    <!-- Bagian kanan: tombol -->
                    <div class="ms-3">
                        <a href="#" class="btn btn-light btn-sm shadow-sm px-3 py-2 text-decoration-none d-flex align-items-center text-secondary" onmouseover="this.classList.add('text-dark')" onmouseout="this.classList.remove('text-dark')">
                            <i class="fas fa-download me-2 text-danger"></i>
                            <span class="fw-semibold px-2">Download Template</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload Excel File</h3>
                </div>
                {{-- {{ route('import.data') }} --}}
                <form action="{{ route('import-data.import.puskesmas') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="excel_file">Choose Excel File</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv">
                                    <label class="custom-file-label" for="excel_file">Choose file</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Supported formats: .xlsx, .xls, .csv</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload & Import
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .custom-file-label::after {
        content: "Browse";
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Update file input label with selected filename
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@stop