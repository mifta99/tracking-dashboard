@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 style="font-size: 24px;">Dashboard Tracking Project T-Piece</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
                        @if (session('status'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('status') }}
                </div>
            @endif
            <div class="bg-primary text-white p-1 rounded ps-1 font-weight-bold mb-3">
                Distribusi Jumlah Alat Kesehatan
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #09c0d8; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Card 1</h4>
                                <p class="text-white mb-0">Content for card 1</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #30db58; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Jumlah Puskesmas</h4>
                                <p class="text-white mb-0">{{ $countPuskesmas }}</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #f7c531; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Card 3</h4>
                                <p class="text-white mb-0">Content for card 3</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #e02b3d; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Card 4</h4>
                                <p class="text-white mb-0">Content for card 4</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-cog"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            Tracking Summary
                        </div>
                        <div class="card-body">
                            <canvas id="pieChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            Monthly Issue Report
                        </div>
                        <div class="card-body">
                            <canvas id="barChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-primary text-white p-1 rounded ps-1 font-weight-bold mb-3">
               Data Pengiriman
            </div>

            <div class="row mb-2">
                <div class="col-md-2">
                    <select class="form-control form-control-sm" id="provinsi" name="provinsi">
                        <option value="">Pilih Provinsi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" id="kabupaten" name="kabupaten">
                        <option value="">Pilih Kabupaten</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" id="kecamatan" name="kecamatan">
                        <option value="">Pilih Kecamatan</option>
                    </select>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Welcome</h3>
                </div>
                <div class="card-body">
                    <p>{{ __('You are logged in!') }}</p>
                    <!-- Add your dashboard content here -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Add extra stylesheets here --}}
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop
