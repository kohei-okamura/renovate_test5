@extends('pdfs.base')

@push('css')
    <style>
    /* 介護給付費請求書共通 */
    table {
        font-size: 0.9rem;
    }
    td:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    td {
        min-width: 22px;
    }
    </style>
@endpush

@section('title', '介護給付費請求書')

@section('content')
    @isset($invoice)
        <section class="sheet">
            @include('pdfs.billings.ltcs-billing-invoice.invoice')
        </section>
    @endisset
    @foreach($statements as $pdf)
        <section class="sheet">
            @include('pdfs.billings.ltcs-billing-invoice.ltcs-statements.index')
        </section>
    @endforeach
@endsection
