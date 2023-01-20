@extends('pdfs.base')

@push('css')
    <style>
    /* サービス提供実績記録票共通 */
    .outer {
        height: 100%;
        padding: 0 16px;
        width: 100%;
    }
    .outer > *:nth-child(2) {
        margin-top: 48px;
    }
    .outer > *:nth-child(n+3) {
        margin-top: 12px;
    }
    .title-block {
        position: relative;
        text-align: center;
    }
    .title-block > .date {
        position: absolute;
        left: 24px;
    }
    .year,
    .month {
        display: inline-block;
        min-width: 28px;
    }
    .title {
        font-size: 1.3rem;
        text-align: center;
    }
    .format-block {
        margin-top: 4px;
        margin-right: 20px;
    }
    tr {
        height: 28px;
    }
    td.right-justify {
        text-align: right;
        padding-right: 4px
    }
    td:not(:last-of-type),
    .lattice > *:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    .user-table td {
        min-width: 22px;
    }
    .page-table td {
        min-width: 44px;
    }
    .table-like {
        text-align: center;
    }
    .table-like > span {
        display: inline-block;
        min-width: 22px;
    }
    .main-table > tbody > tr:first-of-type {
        border-top: solid 2px #000;
    }
    .main-table > tbody > tr > td:last-of-type {
        text-align: left;
        padding: 0 6px;
    }
    </style>
@endpush

@section('title', 'サービス提供実績記録票')

@section('content')
    @foreach($pdfs as $pdf)
        <section class="sheet">
            @if($pdf->format === \Domain\Billing\DwsBillingServiceReportFormat::homeHelpService())
                @include('pdfs.billings.service-report.home-help-service.index')
            @elseif($pdf->format === \Domain\Billing\DwsBillingServiceReportFormat::visitingCareForPwsd())
                @include('pdfs.billings.service-report.visiting-care-for-pwsd.index')
            @endif
        </section>
    @endforeach
@endsection
