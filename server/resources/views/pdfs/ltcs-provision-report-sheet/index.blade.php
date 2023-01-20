@extends('pdfs.base')

@push('css')
    <style>
    /* サービス提供票共通 */
    .outer {
        height: 100%;
        padding: 0 8px;
        width: 100%;
    }
    .align-end {
        align-items: end;
    }
    .text-right {
        text-align: right;
    }
    td:not(:last-of-type),
    .lattice > *:not(:last-of-type),
    .main-table > tbody > tr:nth-last-of-type(6) > td:nth-of-type(4),
    .main-table > tbody > tr:nth-last-of-type(-n+4) > td:nth-of-type(7) {
        border-right: solid 1px #000;
    }
    .page-table td {
        min-width: 44px;
    }
    .table-like > span {
        display: inline-block;
        min-width: 22px;
    }
    </style>
@endpush

@section('title', 'サービス提供票')

@section('content')
    @foreach($sheet['mains'] as $main)
        <section class="sheet gothic">
            @include('pdfs.ltcs-provision-report-sheet.main', ['data' => $main])
        </section>
    @endforeach
    <section class="sheet gothic">
        @include('pdfs.ltcs-provision-report-sheet.appendix', ['model' => $sheet['appendix']])
    </section>
@endsection
