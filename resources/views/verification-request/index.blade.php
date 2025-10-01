@extends('adminlte::page')

@section('title', 'Verification Request')

@section('content_header')
	<h1>Verification Request (Hanya Puskesmas dengan Tanggal Pengiriman)</h1>
@stop

@section('content')
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Daftar Puskesmas</h3>
			<button id="toggleFilter" class="btn btn-sm btn-outline-secondary " type="button">
				<i class="fas fa-filter mr-1"></i><span class="txt">Tampilkan Filter</span>
			</button>
		</div>
		<div class="card-body ">
			<div id="filterPanel" class="mb-3 collapse-hide p-3 border rounded bg-light">
				<div class="row">
					<div class="col-md-4 mb-2">
						<label class="mb-1 small">Provinsi</label>
						<select id="filter_province" placeholder="Pilih Provinsi..."></select>
					</div>
					<div class="col-md-4 mb-2">
						<label class="mb-1 small">Kabupaten/Kota</label>
						<select id="filter_regency" placeholder="Pilih Kabupaten..."></select>
					</div>
					<div class="col-md-4 mb-2">
						<label class="mb-1 small">Kecamatan</label>
						<select id="filter_district" placeholder="Pilih Kecamatan..."></select>
					</div>
				</div>
				<div class="d-flex gap-2">
					<button id="btnFilter" class="btn btn-primary btn-sm mr-2"><i class="fas fa-search"></i> Filter</button>
					<button id="btnReset" class="btn btn-secondary btn-sm"><i class="fas fa-undo"></i> Reset</button>
					<div id="filterInfo" class="ml-3 small text-muted flex-grow-1"></div>
				</div>
			</div>
			<div class="table-responsive">
				<table id="verificationTable" class="table table-bordered table-striped table-sm" style="font-size:12px;">
					<thead>
						<tr class="text-center">
							<th style="width:40px;">No</th>
							<th>Puskesmas</th>
							<th>Provinsi</th>
							<th>Kabupaten/Kota</th>
							<th>Kecamatan</th>
							<th>Tgl Pengiriman</th>
							<th>Verif Kemenkes</th>
							<th style="width:90px;">Detail</th>
						</tr>
					</thead>
					<tbody>
						@forelse($verificationRequests as $idx => $p)
							@php
								$pengiriman = $p->pengiriman; // hasOne
								$tgl = $pengiriman->tgl_pengiriman ? $pengiriman->tgl_pengiriman->format('d-m-Y') : '-';
								$verified = $pengiriman->verif_kemenkes;
							@endphp
							<tr>
								<td class="text-center align-middle">{{ $idx + 1 }}</td>
								<td class="align-middle">{{ $p->name }}</td>
								<td class="align-middle">{{ $p->district->regency->province->name ?? '-' }}</td>
								<td class="align-middle">{{ $p->district->regency->name ?? '-' }}</td>
								<td class="align-middle">{{ $p->district->name ?? '-' }}</td>
								<td class="text-center align-middle">{{ $tgl }}</td>
								<td class="text-center align-middle">
									@if($verified)
										<span class="badge badge-success">Sudah</span>
									@else
										<span class="badge badge-secondary">Belum</span>
									@endif
								</td>
								<td class="text-center align-middle">
									<a href="{{ route('verification-request.show', $p->id) }}" class=""><i class="fas fa-search"></i></a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="8" class="text-center">
									<div class="alert alert-info mb-0 py-2">Belum ada data puskesmas dengan tanggal pengiriman.</div>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>
@stop

@section('css')
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
	<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
	<style>
		.ts-wrapper.single .ts-control{background:#fff;}
		#filterInfo{min-height:20px;}
		#filterPanel.collapse-hide{display:none;}
	</style>
	</style>
@stop

@section('js')
	<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
	<script>
		window.Laravel = window.Laravel || { routes: {} };
		Laravel.routes.verificationShow = "{{ route('verification-request.show', ['id' => 'ID_PLACEHOLDER']) }}";
	</script>
	<script>
		let dataTable;
		let sProvince, sRegency, sDistrict;

		function initDataTable(){
			if(!$.fn.DataTable.isDataTable('#verificationTable')){
				dataTable = $('#verificationTable').DataTable({
				pageLength: 25,
				ordering: true,
				responsive: false,
				autoWidth:false,
				language: {
					emptyTable: 'Tidak ada data',
					lengthMenu: 'Tampilkan _MENU_ baris',
					search: 'Cari:',
					info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
					infoEmpty: 'Tidak ada data',
					paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
				}
				});
			} else {
				dataTable = $('#verificationTable').DataTable();
			}
		}

		function rebuildTable(rows){
			console.log('Rebuild table with rows:', rows ? rows.length : 'undefined');
			initDataTable();
			dataTable.clear();
			if(!Array.isArray(rows)){
				console.warn('Rows is not array. Raw value:', rows);
				dataTable.rows.add([]).draw();
				$('#verificationTable tbody').html('<tr><td colspan="8" class="text-center"><div class="alert alert-warning mb-0 py-2">Format data tidak valid.</div></td></tr>');
				return;
			}
			if(rows.length === 0){
				dataTable.rows.add([]).draw();
				$('#verificationTable tbody').html('<tr><td colspan="8" class="text-center"><div class="alert alert-info mb-0 py-2">Tidak ada data hasil filter.</div></td></tr>');
				return;
			}
			const mapped = rows.map(function(r,i){
				return [
					(i+1),
					r.name,
					r.province,
					r.regency,
					r.district,
					(r.tgl_pengiriman ?? '-'),
					(r.verif_kemenkes ? '<span class="badge badge-success">Sudah</span>' : '<span class="badge badge-secondary">Belum</span>'),
					('<a href="'+Laravel.routes.verificationShow.replace('ID_PLACEHOLDER', r.id)+'" class=""><i class="fas fa-search"></i></a>')
				];
			});
			dataTable.rows.add(mapped).draw();
		}

		function fetchFiltered(){
			$('#btnFilter').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
			const params = {
				province_id: sProvince.getValue() || null,
				regency_id: (sRegency.getValue() === '' ? null : sRegency.getValue()) || null,
				district_id: (sDistrict.getValue() === '' ? null : sDistrict.getValue()) || null,
			};
			console.log('Filter request params:', params);
			$.ajax({
				url: '{{ route('verification-request.fetch') }}',
				method: 'GET',
				data: params,
				success: function(res){
					console.log('Filter response:', res);
					if(!res){ console.warn('Empty response object'); return; }
					if(res.redirect || (typeof res === 'string' && res.indexOf('<!DOCTYPE') !== -1)){
						alert('Sesi mungkin habis. Silakan muat ulang halaman.');
						return;
					}
					if(res.success){
						rebuildTable(res.data || []);
						updateFilterInfo();
					} else {
						console.warn('Success flag false or missing. Full response:', res);
						rebuildTable([]);
					}
				},
				error: function(xhr){
					console.error('Filter fetch error', xhr.status, xhr.responseText);
					alert('Gagal memuat data');
				},
				complete: function(){
					$('#btnFilter').prop('disabled', false).html('<i class="fas fa-search"></i> Filter');
				}
			});
		}

		function updateFilterInfo(){
			let parts=[];
			const pTxt = getOptionText(sProvince, sProvince.getValue());
			const rTxt = getOptionText(sRegency, sRegency.getValue());
			const dTxt = getOptionText(sDistrict, sDistrict.getValue());
			if(pTxt) parts.push('Provinsi: '+pTxt); 
			if(rTxt) parts.push('Kabupaten: '+rTxt); 
			if(dTxt) parts.push('Kecamatan: '+dTxt);
			$('#filterInfo').text(parts.join(' | '));
		}

		function getOptionText(instance, value){
			if(!value) return '';
			const opt = instance.options[value];
			return opt ? opt.name : '';
		}

		function loadProvinces(){
			$.get('{{ route('api-puskesmas.provinces') }}', function(res){
				if(res && res.data){ sProvince.addOptions(res.data); }
			});
		}
		function loadRegencies(pid){
			sRegency.clearOptions(); sRegency.disable();
			sDistrict.clearOptions(); sDistrict.disable();
			if(!pid){ return; }
			$.get('{{ route('api-puskesmas.regencies') }}', {province_id: pid}, function(res){
				if(res && res.data){
					sRegency.addOption({id:'', name:'Semua Kabupaten'});
					sRegency.addOptions(res.data);
					sRegency.enable();
				}
			});
		}
		function loadDistricts(rid){
			sDistrict.clearOptions(); sDistrict.disable();
			if(!rid){ return; }
			$.get('{{ route('api-puskesmas.districts') }}', {regency_id: rid}, function(res){
				if(res && res.data){
					sDistrict.addOption({id:'', name:'Semua Kecamatan'});
					sDistrict.addOptions(res.data);
					sDistrict.enable();
				}
			});
		}

		$(function(){
			initDataTable();
			// Restore filter visibility state
			const stored = localStorage.getItem('vr_filter_hidden');
			if(stored === '1'){
				$('#filterPanel').addClass('collapse-hide');
				$('#toggleFilter .txt').text('Tampilkan Filter');
			}
			$('#toggleFilter').on('click', function(){
				const panel = $('#filterPanel');
				panel.toggleClass('collapse-hide');
				const hidden = panel.hasClass('collapse-hide');
				$('#toggleFilter .txt').text(hidden ? 'Tampilkan Filter' : 'Sembunyikan Filter');
				localStorage.setItem('vr_filter_hidden', hidden ? '1' : '0');
			});
			// Init selects
			sProvince = new TomSelect('#filter_province', {
				valueField:'id', labelField:'name', searchField:'name', placeholder:'Pilih Provinsi...',
				onChange: function(v){ loadRegencies(v); }
			});
			sRegency = new TomSelect('#filter_regency', {
				valueField:'id', labelField:'name', searchField:'name', placeholder:'Pilih Kabupaten...',
				onChange: function(v){ loadDistricts(v); }
			});
			sDistrict = new TomSelect('#filter_district', {
				valueField:'id', labelField:'name', searchField:'name', placeholder:'Pilih Kecamatan...'
			});
			sRegency.disable();
			sDistrict.disable();
			loadProvinces();

			$('#btnFilter').on('click', function(e){ e.preventDefault(); fetchFiltered(); });
			$('#btnReset').on('click', function(){
				sProvince.clear(); sRegency.clear(); sDistrict.clear();
				sRegency.clearOptions(); sDistrict.clearOptions();
				sRegency.disable(); sDistrict.disable();
				$('#filterInfo').empty();
				// reload initial (unfiltered) view but still only tgl_pengiriman not null
				fetchFiltered();
			});
		});
	</script>
@stop
