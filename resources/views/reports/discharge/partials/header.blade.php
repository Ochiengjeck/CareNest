<div class="header">
    <div class="facility-name">{{ strtoupper($facility['facility_name']) }}</div>
    <div class="facility-address">{{ $formatted_address }}</div>
    <div class="facility-contact">
        @if($facility['email'])Email: {{ $facility['email'] }}@endif
        @if($facility['email'] && $facility['phone']) &nbsp;&nbsp; @endif
        @if($facility['phone'])M:{{ $facility['phone'] }}@endif
    </div>
</div>
