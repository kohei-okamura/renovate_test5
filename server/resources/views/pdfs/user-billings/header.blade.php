@push('css')
    <style>
    .header {
        padding: 0 10mm;
    }
    .font-normal {
        font-size: 12pt;
    }
    .font-large {
        font-size: 16pt;
    }
    .destination,
    .publisher {
        max-width: 8cm;
    }
    </style>
@endpush

<div class="flex justify-between header">
    <div class="destination font-normal">
        <div class="flex column address">
            <span>〒{{ $addr->postcode }}</span>
            <span>{{ \Domain\Common\Prefecture::resolve($addr->prefecture) . $addr->city . $addr->street }}</span>
            <span>{{ $addr->apartment }}</span>
        </div>
        @if(strlen($corporationName) > 0)
            <div>{{ $corporationName }}</div>
        @endif
        <div class="font-large"><span style="margin-right: 8px;">{{ $destinationName }}</span>様</div>
    </div>
    <div class="publisher">
        <div>発行日:{{ $issuedOn }}</div>
        <div class="flex column address">
            <span>〒{{ $office->addr->postcode }}</span>
            <span>{{ \Domain\Common\Prefecture::resolve($office->addr->prefecture) . $office->addr->city . $office->addr->street }}</span>
            <span>{{ $office->addr->apartment }}</span>
        </div>
        <div>{{ $office->name }}</div>
        <div><span style="margin-right: 6px;">TEL:</span>{{ $office->tel }}</div>
    </div>
</div>
