@extends('adminlte::page')

@section('title', 'Verification Request')

@section('content_header')
	<h1>Verification Request</h1>
	<p class="text-muted">Menampilkan hanya Puskesmas yang memiliki dokumen perlu diverifikasi</p>
@stop

@section('content')
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h3 class="card-title mb-0">Daftar Puskesmas</h3>
			<button id="toggleFilter" class="btn btn-sm btn-outline-secondary ml-auto" type="button">
				<i class="fas fa-filter mr-1"></i><span class="txt">Tampilkan Filter</span>
			</button>
		</div>
		<div class="card-body ">
			<div id="filterPanel" class="mb-3 collapse-hide p-3 border rounded bg-light">
				<div class="row">
					<div class="col-md-3 mb-2">
						<label class="mb-1 small">Provinsi</label>
						<select id="filter_province" placeholder="Pilih Provinsi..."></select>
					</div>
					<div class="col-md-3 mb-2">
						<label class="mb-1 small">Kabupaten/Kota</label>
						<select id="filter_regency" placeholder="Pilih Kabupaten..."></select>
					</div>
					<div class="col-md-3 mb-2">
						<label class="mb-1 small">Kecamatan</label>
						<select id="filter_district" placeholder="Pilih Kecamatan..."></select>
					</div>
					<div class="col-md-3 mb-2">
						<label class="mb-1 small">Status Verifikasi</label>
						<div id="filter_status" class="border rounded p-2 bg-white" style="max-height: 200px; overflow-y: auto;">
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_kalibrasi" value="has_kalibrasi">
								<label class="form-check-label small" for="has_kalibrasi">Ada Kalibrasi Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_bast" value="has_bast">
								<label class="form-check-label small" for="has_bast">Ada BAST Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_instalasi" value="has_instalasi">
								<label class="form-check-label small" for="has_instalasi">Ada Instalasi Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_uji_fungsi" value="has_uji_fungsi">
								<label class="form-check-label small" for="has_uji_fungsi">Ada Uji Fungsi Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_pelatihan" value="has_pelatihan">
								<label class="form-check-label small" for="has_pelatihan">Ada Pelatihan Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_aspak" value="has_aspak">
								<label class="form-check-label small" for="has_aspak">Ada ASPAK Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="has_basto" value="has_basto">
								<label class="form-check-label small" for="has_basto">Ada BASTO Perlu Verifikasi</label>
							</div>
							<hr class="my-2">
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="count_1" value="count_1">
								<label class="form-check-label small" for="count_1">Hanya 1 Dokumen Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="count_2" value="count_2">
								<label class="form-check-label small" for="count_2">2 Dokumen Perlu Verifikasi</label>
							</div>
							<div class="form-check form-check-sm mb-1">
								<input class="form-check-input status-filter" type="checkbox" id="count_3_plus" value="count_3_plus">
								<label class="form-check-label small" for="count_3_plus">3+ Dokumen Perlu Verifikasi</label>
							</div>
						</div>
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
							<th>Status Verifikasi</th>
							<th style="width:90px;">Detail</th>
						</tr>
					</thead>
					<tbody>
						<!-- Data will be loaded via AJAX -->
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
		
		/* Status filter styling */
		#filter_status {
			border: 2px solid #e3f2fd;
			font-size: 12px;
		}
		#filter_status:focus-within {
			border-color: #1976d2;
		}
		
		.form-check-sm .form-check-input {
			margin-top: 0.125rem;
		}
		
		.form-check-sm .form-check-label {
			font-size: 11px;
			line-height: 1.3;
		}
		
		/* Loading indicator styles */
		.table-loading-overlay {
			position: relative;
		}
		.table-loading-overlay::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: rgba(255, 255, 255, 0.8);
			z-index: 999;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.table-loading-overlay::after {
			content: '\f110';
			font-family: 'Font Awesome 5 Free';
			font-weight: 900;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			font-size: 24px;
			color: #007bff;
			animation: fa-spin 1s infinite linear;
			z-index: 1000;
		}
		
		
		.btn-loading {
			pointer-events: none;
			opacity: 0.7;
		}
		
		.filter-loading {
			background-color: #e9ecef;
			pointer-events: none;
		}
		
		/* Enhanced search input styling */
		.dataTables_filter input {
			border-radius: 4px !important;
			border: 1px solid #ced4da !important;
			padding: 0.375rem 0.75rem !important;
			transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
			width: 250px !important;
		}
		
		.dataTables_filter input:focus {
			border-color: #80bdff !important;
			outline: 0 !important;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
		}
		
		.dataTables_filter input.searching {
			border-color: #ffc107 !important;
			box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
		}
		
		.dataTables_filter {
			margin-bottom: 1rem !important;
		}
		
		.search-info {
			font-size: 0.75rem;
			margin-top: 0.25rem;
			min-height: 1rem;
			font-style: italic;
			transition: color 0.3s ease;
		}
		
		/* Typing indicator */
		.dataTables_filter input.typing {
			border-color: #6c757d;
			box-shadow: 0 0 0 0.1rem rgba(108, 117, 125, 0.2);
		}
		
		/* Search ready indicator */
		.dataTables_filter input.ready-to-search {
			border-color: #28a745;
			box-shadow: 0 0 0 0.1rem rgba(40, 167, 69, 0.2);
		}
		
		/* Document verification badges */
		.badge-sm {
			font-size: 0.7em !important;
			padding: 0.25em 0.5em !important;
			font-weight: 600 !important;
		}
		
		.verification-badges .badge {
			margin: 2px;
			white-space: nowrap;
			font-size: 10px !important;
		}
		
		.pending-count-badge {
			font-size: 11px !important;
			font-weight: 600 !important;
			padding: 0.3em 0.6em !important;
			background-color: #6c757d !important;
			border-color: #6c757d !important;
			color: #fff !important;
		}
		
		.badge-count-soft {
			background-color: #f8f9fa !important;
			color: #6c757d !important;
			border: 1px solid #dee2e6 !important;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
		}
		
		/* Alternative soft colors */
		.badge-count-blue-soft {
			background-color: #e3f2fd !important;
			color: #1976d2 !important;
			border: 1px solid #bbdefb !important;
		}
		
		.badge-count-green-soft {
			background-color: #e8f5e8 !important;
			color: #2e7d32 !important;
			border: 1px solid #c8e6c9 !important;
		}
	</style>
@stop

@section('js')
	<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
	<script>
		window.Laravel = window.Laravel || { routes: {} };
		Laravel.routes.verificationShow = "{{ route('verification-request.detail', ['id' => 'ID_PLACEHOLDER']) }}";
	</script>
	<script>
		let dataTable;
		let sProvince, sRegency, sDistrict;

		function initDataTable(){
			try {
				// Ensure we're starting fresh
				if($.fn.DataTable.isDataTable('#verificationTable')){
					$('#verificationTable').DataTable().destroy();
					$('#verificationTable').removeClass('dataTable');
				}
				
				dataTable = $('#verificationTable').DataTable({
					serverSide: true,
					searchDelay: 1000, // Increase delay to 1 second
					ajax: {
						url: '{{ route('verification-request.fetch', request('status')) }}',
						type: 'GET',
						data: function(d) {
							// Add filter parameters
							d.province_id = sProvince ? sProvince.getValue() || null : null;
							d.regency_id = sRegency ? (sRegency.getValue() === '' ? null : sRegency.getValue()) || null : null;
							d.district_id = sDistrict ? (sDistrict.getValue() === '' ? null : sDistrict.getValue()) || null : null;
							// Get checked status filters
							var statusFilters = [];
							$('.status-filter:checked').each(function() {
								statusFilters.push($(this).val());
							});
							d.status_filter = statusFilters.length > 0 ? statusFilters.join(',') : null;
							
							// Optimize search by trimming whitespace
							if(d.search && d.search.value) {
								d.search.value = d.search.value.trim();
							}
							console.log('DataTable request:', d);
						},
						dataSrc: function(json) {
							console.log('DataTable response:', json);
							if(!json || typeof json !== 'object') {
								console.error('Invalid JSON response:', json);
								return [];
							}
							return json.data || [];
						},
						beforeSend: function() {
							$('.table-responsive').addClass('table-loading-overlay');
						},
						complete: function() {
							$('.table-responsive').removeClass('table-loading-overlay');
						},
						error: function(xhr, status, error) {
							console.error('DataTable AJAX error:', error, xhr.responseText);
							$('.table-responsive').removeClass('table-loading-overlay');
							$('#btnFilter').removeClass('btn-loading').html('<i class="fas fa-search"></i> Filter');
							$('#filterPanel').removeClass('filter-loading');
							
							if(xhr.status === 401 || xhr.responseText.indexOf('<!DOCTYPE') !== -1) {
								alert('Sesi mungkin habis. Silakan muat ulang halaman.');
							} else {
								alert('Gagal memuat data: ' + (error || 'Unknown error'));
							}
						}
					},
					columns: [
						{ 
							data: null, 
							name: 'number', 
							orderable: false, 
							searchable: false,
							render: function(data, type, row, meta) {
								return meta.row + meta.settings._iDisplayStart + 1;
							}
						},
						{ data: 'name', name: 'name' },
						{ data: 'province', name: 'province', orderable: false },
						{ data: 'regency', name: 'regency', orderable: false },
						{ data: 'district', name: 'district', orderable: false },
						{ 
							data: 'tgl_pengiriman', 
							name: 'tgl_pengiriman',
							className: 'text-center',
							render: function(data) {
								return data || '-';
							}
						},
						{ 
							data: 'pending_docs', 
							name: 'pending_verification',
							className: 'text-center',
							orderable: false,
							render: function(data, type, row) {
								if (!row.has_pending_verification) {
									return '<span class="badge badge-success" style="font-size: 11px; padding: 0.3em 0.6em;">✓ Semua Terverifikasi</span>';
								}
								
								let html = '<div class="mb-2">';
								html += '<span class="badge badge-count-blue-soft pending-count-badge">' + row.pending_count + ' Dokumen Perlu Verifikasi</span>';
								html += '</div>';
								
								if (data && data.length > 0) {
									html += '<div class="verification-badges">';
									const badges = data.map(doc => {
										let badgeClass = 'badge-secondary';
										switch(doc) {
											case 'Kalibrasi': 
												badgeClass = 'badge-primary'; 
												break;
											case 'BAST': 
												badgeClass = 'badge-info'; 
												break;
											case 'Instalasi': 
												badgeClass = 'badge-warning text-dark'; 
												break;
											case 'Uji Fungsi': 
												badgeClass = 'badge-success'; 
												break;
											case 'Pelatihan': 
												badgeClass = 'badge-dark'; 
												break;
											case 'ASPAK': 
												badgeClass = 'badge-danger'; 
												break;
											case 'BASTO': 
												badgeClass = 'badge-secondary'; 
												break;
										}
										return '<span class="badge ' + badgeClass + ' badge-sm m-1" title="' + doc + '">' + doc + '</span>';
									});
									html += badges.join('');
									html += '</div>';
								}
								
								return html;
							}
						},
						{ 
							data: 'id', 
							name: 'actions',
							className: 'text-center',
							orderable: false,
							searchable: false,
							render: function(data) {
								const detailUrl = Laravel.routes.verificationShow.replace('ID_PLACEHOLDER', data);
								return '<a href="' + detailUrl + '" title="Lihat Detail"><i class="fas fa-search"></i></a>';
							}
						}
					],
					pageLength: 25,
					lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
					lengthChange: true,
					ordering: true,
					info: true,
					responsive: false,
					autoWidth: false,
					destroy: true,
					searching: true,
					stateSave: false,
					deferRender: true,
					language: {
						emptyTable: 'Tidak ada puskesmas dengan dokumen yang perlu diverifikasi',
						lengthMenu: 'Tampilkan _MENU_ baris',
						search: 'Cari:',
						info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
						infoEmpty: 'Tidak ada data',
						infoFiltered: '(difilter dari _MAX_ total data)',
						paginate: { 
							previous: 'Sebelumnya', 
							next: 'Berikutnya',
							first: 'Pertama',
							last: 'Terakhir'
						},
						zeroRecords: 'Tidak ada data yang cocok'
					},
					order: [[1, 'asc']], // Sort by Puskesmas name
					initComplete: function() {
						// Optimize search input behavior
						var searchInput = $('.dataTables_filter input');
						searchInput.off('keyup search input');
						
						// Add placeholder and attributes
						searchInput.attr('placeholder', 'Cari nama puskesmas, provinsi, kabupaten, kecamatan...');
						searchInput.attr('autocomplete', 'off');
						
						// Add search info element
						if (!$('.search-info').length) {
							searchInput.after('<div class="search-info">Minimal 2 karakter untuk pencarian</div>');
						}
						
						// Add intelligent debounced search with validation
						var searchTimeout;
						var typingTimeout;
						var isTyping = false;
						var lastSearchTerm = '';
						
						searchInput.on('input', function() {
							var searchTerm = this.value.trim();
							var $this = $(this);
							
							// Clear previous timeouts
							clearTimeout(searchTimeout);
							clearTimeout(typingTimeout);
							
							// Set typing indicator
							isTyping = true;
							
							// Visual feedback for minimum characters
							$this.removeClass('searching ready-to-search typing');
							
							if (searchTerm.length > 0 && searchTerm.length < 2) {
								$this.addClass('searching');
								$('.search-info').text('Minimal 2 karakter untuk pencarian').css('color', '#ffc107');
								return;
							} else if (searchTerm.length >= 2) {
								$this.addClass('typing');
								$('.search-info').text('Mengetik...').css('color', '#6c757d');
							} else {
								$('.search-info').text('').css('color', '#6c757d');
							}
							
							// Detect when user stops typing
							typingTimeout = setTimeout(function() {
								isTyping = false;
								$this.removeClass('typing');
								
								// Only search if term has changed and user stopped typing
								if (searchTerm !== lastSearchTerm && (searchTerm.length >= 2 || searchTerm.length === 0)) {
									$this.addClass('ready-to-search');
									$('.search-info').text('Mencari...').css('color', '#007bff');
									dataTable.search(searchTerm).draw();
									lastSearchTerm = searchTerm;
								} else if (searchTerm === '' && lastSearchTerm !== '') {
									// Clear search immediately when input is empty
									$('.search-info').text('Mencari...').css('color', '#007bff');
									dataTable.search('').draw();
									lastSearchTerm = '';
								} else if (searchTerm.length >= 2) {
									$this.addClass('ready-to-search');
									$('.search-info').text('Siap mencari (tekan Enter)').css('color', '#28a745');
								}
							}, 1000); // Wait 1 second after user stops typing
						});
						
						// Immediate search on Enter key
						searchInput.on('keypress', function(e) {
							if(e.which === 13) { // Enter key
								var searchTerm = this.value.trim();
								
								// Clear all timeouts and force search
								clearTimeout(searchTimeout);
								clearTimeout(typingTimeout);
								isTyping = false;
								
								if (searchTerm.length >= 2 || searchTerm.length === 0) {
									$('.search-info').text('Mencari...').css('color', '#007bff');
									dataTable.search(searchTerm).draw();
									lastSearchTerm = searchTerm;
								}
							}
						});
						
						// Add focus and blur handlers for better UX
						searchInput.on('focus', function() {
							if (this.value.trim().length === 0) {
								$('.search-info').text('Ketik minimal 2 karakter untuk mencari').css('color', '#6c757d');
							}
						});
						
						searchInput.on('blur', function() {
							if (this.value.trim().length === 0) {
								$('.search-info').text('');
							}
						});
						
						// Clear search feedback after draw
						dataTable.on('draw', function() {
							setTimeout(function() {
								$('.search-info').text('');
								searchInput.removeClass('searching');
							}, 100);
						});
						
						console.log('DataTable initialized with enhanced search optimization');
					}
				});
			} catch (e) {
				console.error('DataTable initialization error:', e);
				// Remove any partial DataTable classes/attributes
				$('#verificationTable').removeClass('dataTable');
				$('#verificationTable_wrapper').remove();
			}
		}



		function applyFilter(){
			if(dataTable){
				// Show loading state
				$('#btnFilter').addClass('btn-loading').html('<i class="fas fa-spinner fa-spin"></i> Memuat...');
				$('#filterPanel').addClass('filter-loading');
				$('.table-responsive').addClass('table-loading-overlay');
				
				// Reload DataTable with current filter values
				dataTable.ajax.reload(function() {
					// Hide loading state when complete
					$('#btnFilter').removeClass('btn-loading').html('<i class="fas fa-search"></i> Filter');
					$('#filterPanel').removeClass('filter-loading');
					$('.table-responsive').removeClass('table-loading-overlay');
					updateFilterInfo();
				}, false); // false = don't reset paging
			}
		}

		function updateFilterInfo(){
			let parts=[];
			const pTxt = getOptionText(sProvince, sProvince.getValue());
			const rTxt = getOptionText(sRegency, sRegency.getValue());
			const dTxt = getOptionText(sDistrict, sDistrict.getValue());
			
			// Get selected status filters
			let statusTexts = [];
			$('.status-filter:checked').each(function() {
				statusTexts.push($(this).next('label').text());
			});
			
			if(pTxt) parts.push('Provinsi: '+pTxt); 
			if(rTxt) parts.push('Kabupaten: '+rTxt); 
			if(dTxt) parts.push('Kecamatan: '+dTxt);
			if(statusTexts.length > 0) parts.push('Status: ' + statusTexts.length + ' filter aktif');
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
			// Initialize DataTable immediately
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
			
			// Add change event listeners to status checkboxes
			$('.status-filter').on('change', function() {
				// Auto-apply filter when checkbox changes (optional - you can remove this if you want manual filter button)
				// applyFilter();
			});
			
			sRegency.disable();
			sDistrict.disable();
			loadProvinces();

			$('#btnFilter').on('click', function(e){ e.preventDefault(); applyFilter(); });
			$('#btnReset').on('click', function(){
				// Show loading state
				$(this).addClass('btn-loading').html('<i class="fas fa-spinner fa-spin"></i> Reset...');
				$('#filterPanel').addClass('filter-loading');
				$('.table-responsive').addClass('table-loading-overlay');
				
				sProvince.clear(); sRegency.clear(); sDistrict.clear();
				$('.status-filter').prop('checked', false); // Uncheck all status filters
				sRegency.clearOptions(); sDistrict.clearOptions();
				sRegency.disable(); sDistrict.disable();
				$('#filterInfo').empty();
				
				// Reload table with cleared filters
				if(dataTable) {
					const resetBtn = $(this);
					dataTable.ajax.reload(function() {
						// Hide loading state when complete
						resetBtn.removeClass('btn-loading').html('<i class="fas fa-undo"></i> Reset');
						$('#filterPanel').removeClass('filter-loading');
						$('.table-responsive').removeClass('table-loading-overlay');
					}, false);
				}
			});
		});
	</script>
@stop
