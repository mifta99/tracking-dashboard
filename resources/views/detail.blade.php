@extends('adminlte::page')

@section('title', 'Detail')

@section('content_header')
    <h1 style="font-size: 24px;">Detail Puskesmas</h1>
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

            <div class="row">
                <div class="col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header bg-primary text-white d-flex align-items-center">
                            <h3 class="card-title mb-0">Basic Information</h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4 font-weight-bold">Provinsi</div>
                                <div class="col-sm-8">: Jawa Timur</div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 font-weight-bold">Kabupaten/Kota</div>
                                <div class="col-sm-8">: Kota Surabaya</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Kecamatan</div>
                                <div class="col-sm-8">: Wiyung</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Nama Puskesmas</div>
                                <div class="col-sm-8">: Puskesmas Wiyung</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">PIC Puskesmas</div>
                                <div class="col-sm-8">: Budi</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Kepala Puskesmas</div>
                                <div class="col-sm-8">: Budi</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">PIC Dinas Kesehatan Kabupaten/Kota</div>
                                <div class="col-sm-8">: Budi</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">PIC Dinas Kesehatan Provinsi</div>
                                <div class="col-sm-8">: Budi</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header bg-success text-white d-flex align-items-center">
                            <h3 class="card-title mb-0">Delivery Information</h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-success btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4 font-weight-bold">Tanggal Pengiriman</div>
                                <div class="col-sm-8">: 01 Januari 2023</div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 font-weight-bold">ETA</div>
                                <div class="col-sm-8">: 3 Hari</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">RESI</div>
                                <div class="col-sm-8">: 855669812236</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Serial Number</div>
                                <div class="col-sm-8">: 4787891625</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Target Alat Diterima</div>
                                <div class="col-sm-8">: 4 Januari 2023</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Nama Penerima</div>
                                <div class="col-sm-8">: Siti</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Jabatan Penerima</div>
                                <div class="col-sm-8">: Staff</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Instansi Penerima</div>
                                <div class="col-sm-8">: Puskesmas</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Nomor HP Penerima</div>
                                <div class="col-sm-8">: 08123456789</div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Bukti Tanda Terima</div>
                                <div class="col-sm-8">: <a href="#" class="text-primary">Download</a></div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-4 font-weight-bold">Catatan</div>
                                <div class="col-sm-8">: Lorem ipsum dolor sit amet</div>
                            </div>
                            <div class="row mt-3 mb-3">
                                <span class="flex-grow-1 border-bottom border-dashed mx-2"></span>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 font-weight-bold">Verifikasi Kemenkes</div>
                                <div class="col-sm-8">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="verified" disabled>
                                            <label class="custom-control-label" for="verified">Verified</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            Uji Fungsi
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3 border-right border-bottom">
                                    <div class="row">
                                        <div class="col-sm-4 font-weight-bold">Tanggal Instalasi</div>
                                        <div class="col-sm-8">: 01 Januari 2023</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-sm-4 font-weight-bold">Berita Acara Instalasi</div>
                                        <div class="col-sm-8">: <a href="#" class="text-primary">Download</a></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 font-weight-bold">Target Tanggal Uji Fungsi</div>
                                        <div class="col-sm-8">: 01 Januari 2023</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 font-weight-bold">Tanggal Uji Fungsi</div>
                                        <div class="col-sm-8">: 01 Januari 2023</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-sm-4 font-weight-bold">Berita Acara Uji Fungsi</div>
                                        <div class="col-sm-8">: <a href="#" class="text-primary">Download</a></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 font-weight-bold">Tanggal Pelatihan Alat</div>
                                        <div class="col-sm-8">: 01 Januari 2023</div>
                                    </div>
                                    <div class="row mb-1">
                                        <div class="col-sm-4 font-weight-bold">Berita Acara Pelatihan Alat</div>
                                        <div class="col-sm-8">: <a href="#" class="text-primary">Download</a></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4 font-weight-bold">Catatan</div>
                                        <div class="col-sm-8">: Lorem ipsum dolor sit amet</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 border-bottom">
                                    <div class="row ml-2">
                                        <div class="col-sm-4 font-weight-bold">Verifikasi Kemenkes</div>
                                        <div class="col-sm-8">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="verified" disabled>
                                                    <label class="custom-control-label" for="verified">Verified</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6 border-right">
                                    </div>
                                    <div class="col-md-6 ">
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            Delivery Progress
                        </div>
                        <div class="card-body">

                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
@stop

@section('css')
    {{-- Add extra stylesheets here --}}
@stop

@section('js')
    <script>
        console.log('Detail page loaded');
    </script>
@stop
