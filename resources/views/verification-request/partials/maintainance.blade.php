    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 pr-1 bg-info text-white d-flex align-items-center">
                    <span class="section-title-bar">Maintainance</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="maintenance-table" style="font-size: 13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:56px;">No</th>
                                    <th>Layanan</th>
                                    <th style="width:120px;">Kunjungan</th>
                                    <th style="width:220px;">Waktu Pengecekan</th>
                                    <th style="width:130px;">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 pr-1 bg-warning text-white d-flex align-items-center">
                    <span class="section-title-bar">Kalibrasi</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="calibration-table" style="font-size: 13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:56px;">No</th>
                                    <th>Kuartal</th>
                                    <th>Deskripsi</th>
                                    <th style="width:130px;">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .status-check { display:inline-flex; align-items:center; gap:6px; font-weight:600; }
        .status-check i { font-size: 1rem; }
        .status-done { color:#28a745; }
        .status-pending { color:#adb5bd; }
        @media (max-width: 576px){
            #maintenance-table thead th:nth-child(3),
            #maintenance-table tbody td:nth-child(3){ display:none; }
        }
    </style>

    <script>
    (function(){
        // Front-end only sample data
        const maintainData = [
        @foreach($maintenance as $maintain)
            { layanan: '{{ $maintain->layanan }}', kunjungan: '{{ $maintain->kunjungan }}', waktu: '{{ $maintain->waktu_pengecekan }}', done: {{ $maintain->is_done ? 'true' : 'false' }} },
        @endforeach
        ];
        const kalibData = [
            @foreach($kalibrasi as $kalib)
            { kuartal: 'Q{{ $kalib->kuartal }}', deskripsi: '{{ $kalib->description }}', done: {{ $kalib->is_done ? 'true' : 'false' }} },
            @endforeach
        ];

        const mtb = document.querySelector('#maintenance-table tbody');
        const ktb = document.querySelector('#calibration-table tbody');
        if (mtb) {
            mtb.innerHTML = maintainData.map((row, idx) => `
                <tr>
                    <td class="text-center align-middle">${idx+1}</td>
                    <td class="align-middle">${row.layanan}</td>
                    <td class="align-middle text-center">Ke-${row.kunjungan}</td>
                    <td class="align-middle">${row.waktu}</td>
                    <td class="align-middle text-center">
                        <span class="status-check ${row.done ? 'status-done' : 'status-pending'}">
                            <i class="fas ${row.done ? 'fa-check-circle' : 'fa-circle'}"></i>
                            ${row.done ? 'Selesai' : 'Belum'}
                        </span>
                    </td>
                </tr>
            `).join('');
        }
        if (ktb) {
            ktb.innerHTML = kalibData.map((row, idx) => `
                <tr>
                    <td class="text-center align-middle">${idx+1}</td>
                    <td class="align-middle">${row.kuartal}</td>
                    <td class="align-middle">${row.deskripsi}</td>
                    <td class="align-middle text-center">
                        <span class="status-check ${row.done ? 'status-done' : 'status-pending'}">
                            <i class="fas ${row.done ? 'fa-check-circle' : 'fa-circle'}"></i>
                            ${row.done ? 'Selesai' : 'Belum'}
                        </span>
                    </td>
                </tr>
            `).join('');
        }
    })();
    </script>
