@extends('adminlte::page')

@section('title', 'Kilat - Import PIC')

@section('content_header')
    <h1 style="font-size: 22px;">Kilat - Import PIC dari Excel</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Unggah Excel</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <label for="excel_file" class="mb-1">Pilih File Excel</label>
                        <input type="file" class="form-control" id="excel_file" accept=".xlsx,.xls,.csv">
                        <small class="text-muted">Kolom yang dibaca: <code>id_puskesmas</code> dan <code>data_pic</code></small>
                    </div>
                    <button id="btn-load" class="btn btn-primary">
                        <i class="fas fa-file-upload"></i> Muat Data
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar dari Excel</h3>
                    <div>
                        <button id="btn-start" class="btn btn-success btn-sm" disabled>
                            <i class="fas fa-play"></i> Start
                        </button>
                        <button id="btn-reset" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" id="result-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 220px;">ID Puskesmas</th>
                                    <th>Nama (data_pic)</th>
                                    <th style="width: 40%;">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="placeholder-row"><td colspan="3" class="text-center text-muted">Belum ada data. Unggah Excel lalu klik "Muat Data".</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="progress" style="height: 8px; display:none;" id="progress-wrap">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" id="progress-bar"></div>
                        </div>
                    </div>
                    <small class="ml-2" id="progress-text"></small>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    #result-table td, #result-table th { vertical-align: middle; }
    .result-ok { color: #155724; }
    .result-err { color: #721c24; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    .truncate { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; }
    .cell-wrap { white-space: normal; }
    .small-muted { font-size: 12px; color: #6c757d; }
    .spinner { width: 1rem; height: 1rem; border: 2px solid #e9ecef; border-top-color: #17a2b8; border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .minh-56 { min-height: 56px; }
    .w-100p { width: 100%; }
</style>
@stop

@section('js')
<script>
(() => {
    const CSRF = '{{ csrf_token() }}';
    const SHOW_URL = '{{ route('kilat.api.show') }}';
    const IMPORT_URL = '{{ route('kilat.api.import') }}';

    const $file = document.getElementById('excel_file');
    const $btnLoad = document.getElementById('btn-load');
    const $btnStart = document.getElementById('btn-start');
    const $btnReset = document.getElementById('btn-reset');
    const $tbody = document.querySelector('#result-table tbody');
    const $placeholder = document.getElementById('placeholder-row');
    const $progressWrap = document.getElementById('progress-wrap');
    const $progressBar = document.getElementById('progress-bar');
    const $progressText = document.getElementById('progress-text');

    let rows = []; // [{id_puskesmas, data_pic, idx}]
    let running = false;

    function clearTable() {
        rows = [];
        $tbody.innerHTML = '';
        const tr = document.createElement('tr');
        tr.id = 'placeholder-row';
        const td = document.createElement('td');
        td.colSpan = 3;
        td.className = 'text-center text-muted';
        td.textContent = 'Belum ada data. Unggah Excel lalu klik "Muat Data".';
        tr.appendChild(td);
        $tbody.appendChild(tr);
        $btnStart.disabled = true;
        updateProgress(0, 0);
        $progressWrap.style.display = 'none';
        $progressText.textContent = '';
    }

    function renderRows(data) {
        $tbody.innerHTML = '';
        data.forEach((row, i) => {
            const tr = document.createElement('tr');
            tr.dataset.idx = i;

            const tdId = document.createElement('td');
            tdId.className = 'mono';
            tdId.textContent = row.id_puskesmas ?? '';

            const tdName = document.createElement('td');
            tdName.className = 'cell-wrap';
            tdName.textContent = row.data_pic ?? '';

            const tdRes = document.createElement('td');
            tdRes.className = 'minh-56';
            tdRes.innerHTML = '<span class="small-muted">Menunggu...</span>';

            tr.appendChild(tdId);
            tr.appendChild(tdName);
            tr.appendChild(tdRes);
            $tbody.appendChild(tr);
        });
        $btnStart.disabled = data.length === 0;
    }

    function updateRowResult(idx, html, isError = false) {
        const tr = $tbody.querySelector(`tr[data-idx="${idx}"]`);
        if (!tr) return;
        const td = tr.children[2];
        td.classList.toggle('result-err', !!isError);
        td.classList.toggle('result-ok', !isError);
        td.innerHTML = html;
    }

    function updateProgress(done, total) {
        if (total <= 0) {
            $progressBar.style.width = '0%';
            $progressText.textContent = '';
            return;
        }
        const pct = Math.round((done / total) * 100);
        $progressBar.style.width = pct + '%';
        $progressText.textContent = `${done}/${total} (${pct}%)`;
    }

    async function handleLoad() {
        if (!$file.files || $file.files.length === 0) {
            alert('Pilih file Excel terlebih dahulu.');
            return;
        }
        const form = new FormData();
        form.append('excel_file', $file.files[0]);
        form.append('_token', CSRF);

        $btnLoad.disabled = true;
        $btnStart.disabled = true;
        $tbody.innerHTML = '<tr><td colspan="3" class="text-center"><span class="spinner"></span> Memuat data...</td></tr>';

        try {
            const resp = await fetch(SHOW_URL, { method: 'POST', body: form });
            const json = await resp.json();
            if (!json || json.success !== true || !Array.isArray(json.data)) {
                throw new Error(json && json.message ? json.message : 'Gagal memuat data');
            }

            // Hanya ambil kolom yang dibutuhkan
            const mapped = json.data.map((it, idx) => ({
                id_puskesmas: it.id_puskesmas ?? it.ID_PUSKESMAS ?? it.id ?? '',
                data_pic: it.data_pic ?? it.name ?? it.nama ?? '' ,
                idx
            })).filter(r => (r.id_puskesmas || '').toString().trim() !== '' && (r.data_pic || '').toString().trim() !== '');

            rows = mapped;
            if (rows.length === 0) {
                $tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Tidak ada baris valid dengan kolom id_puskesmas dan data_pic.</td></tr>';
                $btnStart.disabled = true;
                return;
            }
            renderRows(rows);
        } catch (e) {
            console.error(e);
            $tbody.innerHTML = `<tr><td colspan="3" class="text-center text-danger">${e.message || 'Terjadi kesalahan saat memuat data'}</td></tr>`;
        } finally {
            $btnLoad.disabled = false;
        }
    }

    async function handleStart() {
        if (running) return;
        if (!rows || rows.length === 0) return;
        running = true;
        $btnStart.disabled = true;
        $progressWrap.style.display = '';
        updateProgress(0, rows.length);

        let done = 0;
        for (let i = 0; i < rows.length; i++) {
            const r = rows[i];
            updateRowResult(r.idx, '<span class="spinner"></span> Memproses...');
            try {
                const resp = await fetch(IMPORT_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id_puskesmas: r.id_puskesmas,
                        name: r.data_pic
                    })
                });
                const json = await resp.json();
                if (resp.ok && json && json.success) {
                    const d = json.data || {};
                    const detail = [d.nama, d.jabatan, d.nomor_hp].filter(Boolean).join(' | ');
                    updateRowResult(r.idx, detail ? `<span class="result-ok">OK</span><br><span class="small">${detail}</span>` : '<span class="result-ok">OK</span>');
                } else {
                    const msg = (json && (json.message || (json.raw_response && json.raw_response.message))) || 'Gagal memproses';
                    updateRowResult(r.idx, `<span class="result-err">${msg}</span>`, true);
                }
            } catch (e) {
                updateRowResult(r.idx, `<span class="result-err">${e.message || 'Error jaringan'}</span>`, true);
            } finally {
                done++;
                updateProgress(done, rows.length);
            }
        }

        running = false;
    }

    function handleReset() {
        if (running) return;
        $file.value = '';
        clearTable();
    }

    // Init
    clearTable();

    // Events
    $btnLoad.addEventListener('click', handleLoad);
    $btnStart.addEventListener('click', handleStart);
    $btnReset.addEventListener('click', handleReset);
})();
</script>
@stop
