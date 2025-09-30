@php $on = $on ?? false; @endphp
<span class="badge {{ $on ? 'badge-success' : 'badge-secondary' }} d-inline-flex align-items-center" style="gap:4px;">
    <i class="fas {{ $on ? 'fa-check' : 'fa-minus' }}"></i> {{ $on ? 'Verified' : 'Pending' }}
</span>
