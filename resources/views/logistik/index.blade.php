@extends('adminlte::page')

@section('title', 'Logistik')

@section('content_header')
    <h1>Logistik</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
<div class="container-fluid" id="logistics-dashboard"
     data-endpoint-lookup="{{ route('logistik.get-puskesmas-by-resi') }}"
     data-endpoint-upload="{{ route('logistik.upload-bast') }}">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Upload BAST Pengiriman</h3>
                </div>
                <div class="card-body">
                    <form id="shipmentLookupForm">
                        <div class="form-group">
                            <label for="resiInput">Nomor Resi Pengiriman</label>
                            <div class="input-group">
                                <input type="text" id="resiInput" class="form-control" placeholder="Masukkan nomor resi" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" id="btnLookup">Cari Pengiriman</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="lookupAlert" class="alert d-none" role="alert"></div>

                    <div id="shipmentDetails" class="mt-4 d-none">
                        <h5 class="font-weight-bold mb-3">Detail Puskesmas</h5>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Provinsi</label>
                                <input type="text" id="fieldProvince" class="form-control" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Kota/Kabupaten</label>
                                <input type="text" id="fieldCity" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Kecamatan</label>
                                <input type="text" id="fieldDistrict" class="form-control" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nama PIC</label>
                                <input type="text" id="fieldPicName" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>No. HP</label>
                                <input type="text" id="fieldPhone" class="form-control" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label>No. HP Alternatif</label>
                                <input type="text" id="fieldAltPhone" class="form-control" disabled>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="bastFile">Upload Berita Acara Serah Terima (BAST)</label>
                            <form id="bastUploadForm" class="border rounded p-3 bg-light">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="bastFile" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label class="custom-file-label" for="bastFile">Pilih file BAST</label>
                                </div>
                                <small class="form-text text-muted mt-2">
                                    Format yang didukung: PDF, JPG, JPEG, PNG. Maksimal 10 MB.
                                </small>
                                <div id="bastPreviewWrapper" class="mt-3 d-none">
                                    <label class="font-weight-bold d-block mb-2">Preview BAST</label>
                                    <div class="border rounded p-3 bg-white text-center">
                                        <img id="bastPreviewImage" src="" alt="Preview BAST" class="img-fluid d-none">
                                        <button type="button" id="bastPreviewImageZoom" class="btn btn-outline-secondary btn-sm mt-2 d-none">
                                            Lihat ukuran penuh
                                        </button>
                                        <div id="bastPreviewPdf" class="d-none">
                                            <span class="fas fa-file-pdf fa-3x text-danger"></span>
                                            <p class="mt-2 mb-1 font-weight-bold" id="bastPreviewPdfName"></p>
                                            <button type="button" id="bastPreviewPdfZoom" class="btn btn-outline-secondary btn-sm d-none">
                                                Lihat ukuran penuh
                                            </button>
                                        </div>
                                        <div id="bastPreviewPlaceholder" class="text-muted">
                                            Belum ada file dipilih.
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <button type="submit" class="btn btn-success" id="btnUploadBast" disabled>
                                        <span class="fas fa-upload mr-1"></span> Upload BAST
                                    </button>
                                    <a href="#" id="bastCurrentLink" class="btn btn-link btn-sm d-none" target="_blank">
                                        <span class="fas fa-file-alt mr-1"></span> Lihat BAST Tersimpan
                                    </a>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Panduan Singkat</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li>Masukkan nomor resi pengiriman, lalu tekan tombol <strong>Cari Pengiriman</strong>.</li>
                        <li>Pastikan detail puskesmas sudah sesuai.</li>
                        <li>Unggah file BAST yang sudah ditandatangani.</li>
                        <li>Gunakan tombol <strong>Lihat ukuran penuh</strong> untuk memverifikasi sebelum dokumentasi lapangan diunggah.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview BAST -->
<div class="modal fade" id="bastPreviewModal" tabindex="-1" role="dialog" aria-labelledby="bastPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bastPreviewModalLabel">Preview BAST</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>bastPreviewUrl
                </button>
            </div>
            <div class="modal-body" id="bastModalContent">
                <div class="text-center text-muted py-5">Tidak ada preview.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto Pickup -->
<div class="modal fade" id="photoModal" tabindex="-1" role="dialog" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Dokumentasi Foto Pickup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="photoModalBody">
                <div class="text-center text-muted">Tidak ada foto untuk resi ini.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    #logistics-dashboard .custom-file-label::after {
        content: 'Pilih';
    }
    #bastPreviewWrapper img {
        max-height: 280px;
        object-fit: contain;
    }
    #bastPreviewWrapper .fas {
        display: block;
    }
    #photoModalBody img {
        max-height: 320px;
        object-fit: contain;
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        const $dashboard = $('#logistics-dashboard');
        const endpoints = {
            lookup: $dashboard.data('endpoint-lookup'),
            upload: $dashboard.data('endpoint-upload'),
            photos: $dashboard.data('endpoint-photos')
        };

        const $lookupAlert = $('#lookupAlert');
        const $details = $('#shipmentDetails');
        const $bastForm = $('#bastUploadForm');
        const $bastButton = $('#btnUploadBast');
        const $bastFile = $('#bastFile');
        const $bastLabel = $bastFile.siblings('.custom-file-label');
        const $bastLink = $('#bastCurrentLink');
        const $bastPreviewWrapper = $('#bastPreviewWrapper');
        const $bastPreviewImage = $('#bastPreviewImage');
        const $bastPreviewImageZoom = $('#bastPreviewImageZoom');
        const $bastPreviewPdf = $('#bastPreviewPdf');
        const $bastPreviewPdfName = $('#bastPreviewPdfName');
        const $bastPreviewPdfZoom = $('#bastPreviewPdfZoom');
        const $bastPreviewPlaceholder = $('#bastPreviewPlaceholder');
        const $bastPreviewModal = $('#bastPreviewModal');
        const $bastModalContent = $('#bastModalContent');
        const $meta = $('#shipmentMeta');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const bastModalEmpty = '<div class="text-center text-muted py-5">Tidak ada preview.</div>';

        let currentShipment = null;
        let bastPreviewUrl = null;

        function toggleAlert(type, message) {
            if (!message) {
                $lookupAlert.addClass('d-none').removeClass('alert-success alert-danger alert-warning alert-info');
                $lookupAlert.text('');
                return;
            }
            $lookupAlert
                .removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass(`alert-${type}`)
                .text(message);
        }

        function resetDetails() {
            $details.addClass('d-none');
            currentShipment = null;
            $meta.text('');
            $bastLink.addClass('d-none').attr('href', '#');
            $bastButton.prop('disabled', true);
            $bastForm[0].reset();
            $bastLabel.text('Pilih file BAST');
            resetBastPreview();
            $('#fieldProvince, #fieldCity, #fieldDistrict, #fieldPicName, #fieldPhone, #fieldAltPhone')
                .val('');
        }

        function resetBastPreview() {
            if (bastPreviewUrl) {
                URL.revokeObjectURL(bastPreviewUrl);
                bastPreviewUrl = null;
            }
            $bastPreviewImage.attr('src', '').addClass('d-none');
            $bastPreviewImageZoom.addClass('d-none');
            $bastPreviewPdf.addClass('d-none');
            $bastPreviewPdfName.text('');
            $bastPreviewPdfZoom.addClass('d-none');
            $bastPreviewPlaceholder
                .removeClass('d-none text-warning')
                .addClass('text-muted')
                .text('Belum ada file dipilih.');
            $bastPreviewWrapper.addClass('d-none');
            $bastModalContent.html(bastModalEmpty);
        }

        function showBastPreview(file) {
            resetBastPreview();
            if (!file) {
                return;
            }

            const mimeType = file.type || '';
            const lowerName = file.name ? file.name.toLowerCase() : '';
            const isImage = mimeType.startsWith('image/');
            const isPdf = mimeType === 'application/pdf' || lowerName.endsWith('.pdf');

            bastPreviewUrl = URL.createObjectURL(file);
            $bastPreviewWrapper.removeClass('d-none');
            $bastPreviewPlaceholder.addClass('d-none');

            if (isImage) {
                $bastPreviewImage.attr('src', bastPreviewUrl).removeClass('d-none');
                $bastPreviewImageZoom.removeClass('d-none');
            } else if (isPdf) {
                $bastPreviewPdfName.text(file.name || 'BAST.pdf');
                $bastPreviewPdf.removeClass('d-none');
                $bastPreviewPdfZoom.removeClass('d-none');
            } else {
                $bastPreviewPlaceholder
                    .removeClass('text-muted')
                    .addClass('text-warning')
                    .text('Format file tidak dapat dipratinjau, namun tetap dapat diunggah.');
            }
        }

        function showBastModal(type) {
            if (!bastPreviewUrl) {
                return;
            }

            let contentHtml = bastModalEmpty;
            if (type === 'image') {
                contentHtml = `<img src="${bastPreviewUrl}" alt="Preview BAST" class="img-fluid w-100">`;
            } else if (type === 'pdf') {
                contentHtml = `<iframe src="${bastPreviewUrl}" title="Preview BAST" class="w-100" style="height:80vh;border:none;"></iframe>`;
            }

            $bastModalContent.html(contentHtml);
            $bastPreviewModal.modal('show');
        }

        function populateDetails(shipment) {
            currentShipment = shipment;
            const puskesmas = shipment?.puskesmas || {};
            const district = shipment?.district || {};
            const regency = district?.regency || {};
            const province = regency?.province || {};

            $('#fieldProvince').val(province?.name || '-');
            $('#fieldCity').val(regency?.name || '-');
            $('#fieldDistrict').val(district?.name || '-');
            $('#fieldPicName').val(puskesmas?.pic || '-');
            $('#fieldPhone').val(puskesmas?.no_hp || '-');
            $('#fieldAltPhone').val(puskesmas?.no_hp_alternatif || '-');

            if (shipment?.bast_url) {
                $bastLink.removeClass('d-none').attr('href', shipment.bast_url);
            } else {
                $bastLink.addClass('d-none').attr('href', '#');
            }

            const meta = [
                shipment?.resi ? `Resi: ${shipment.resi}` : null,
                shipment?.status ? `Status: ${shipment.status}` : null,
                shipment?.updated_at ? `Diperbarui: ${shipment.updated_at}` : null
            ].filter(Boolean).join(' • ');
            $meta.text(meta);

            $details.removeClass('d-none');
            $bastButton.prop('disabled', false);
        }

        function buildPhotoMarkup(photos) {
            if (!Array.isArray(photos) || !photos.length) {
                return '<div class="text-center text-muted py-5">Tidak ada foto yang tersedia untuk resi ini.</div>';
            }

            return photos.map(function (photo, index) {
                const caption = photo.caption || `Foto ${index + 1}`;
                const url = photo.url || photo;
                return `
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-2 h-100">
                            <img src="${url}" alt="${caption}" class="img-fluid w-100 mb-2">
                            <div class="small text-muted">${caption}</div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        $('#shipmentLookupForm').on('submit', function (event) {
            event.preventDefault();
            const resi = $('#resiInput').val().trim();

            if (!resi) {
                toggleAlert('warning', 'Mohon masukkan nomor resi terlebih dahulu.');
                return;
            }

            toggleAlert(null);
            resetDetails();
            toggleAlert('info', 'Mencari data pengiriman...');

            $.ajax({
                url: `${endpoints.lookup}/${encodeURIComponent(resi)}`,
                method: 'GET',
                dataType: 'json'
            }).done(function (response) {
                if (response && response.success && response.data) {
                    populateDetails(response.data);
                    toggleAlert('success', 'Pengiriman ditemukan. Silakan periksa detailnya.');
                } else {
                    const message = response?.message || 'Pengiriman tidak ditemukan atau API tidak memberikan data.';
                    toggleAlert('warning', message);
                }
            }).fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menghubungi API.';
                toggleAlert('danger', message);
            });
        });

        $bastPreviewImageZoom.on('click', function () {
            showBastModal('image');
        });

        $bastPreviewPdfZoom.on('click', function () {
            showBastModal('pdf');
        });

        $bastPreviewModal.on('hidden.bs.modal', function () {
            $bastModalContent.html(bastModalEmpty);
        });

        $bastFile.on('change', function () {
            const fileName = this.files && this.files.length ? this.files[0].name : 'Pilih file BAST';
            $bastLabel.text(fileName);
            if (this.files && this.files.length) {
                showBastPreview(this.files[0]);
            } else {
                resetBastPreview();
            }
        });

        $bastForm.on('submit', function (event) {
            event.preventDefault();
            if (!currentShipment) {
                toggleAlert('warning', 'Cari pengiriman terlebih dahulu sebelum mengunggah BAST.');
                return;
            }
            const fileInput = $bastFile[0];
            if (!fileInput.files.length) {
                toggleAlert('warning', 'Silakan pilih file BAST yang akan diunggah.');
                return;
            }

            const formData = new FormData();
            formData.append('resi', currentShipment.pengiriman.resi);
            formData.append('bast_file', fileInput.files[0]);

            $bastButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2"></span>Mengunggah...');

            $.ajax({
                url: endpoints.upload,
                method: 'POST',
                processData: false,
                contentType: false,
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            }).done(function (response) {
                const message = response?.message || 'BAST berhasil diunggah.';
                toggleAlert('success', message);
                if (response?.data?.bast_url) {
                    $bastLink.removeClass('d-none').attr('href', response.data.bast_url);
                }
            }).fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Gagal mengunggah BAST. Silakan coba kembali.';
                toggleAlert('danger', message);
            }).always(function () {
                $bastButton.prop('disabled', false).html('<span class="fas fa-upload mr-1"></span> Upload BAST');
            });
        });
    });
</script>
@stop
