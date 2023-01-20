@extends('pdfs.base')

@push('css')
    <style>
    /* 介護給付費・訓練等給付費等請求書／明細書共通 */
    body {
        margin: 0;
    }
    .outer {
        border: solid 1px #000;
        height: 100%;
        padding: 14px 12px 14px 18px;
        width: 100%;
    }
    table {
        font-size: 0.9rem;
    }
    .thin-width {
        width: 1rem;
    }
    .format-block {
        margin-bottom: 4px;
        margin-left: 10px;
    }
    .title {
        font-size: 1.3rem;
        text-align: center;
    }
    </style>
@endpush

@section('title', '介護給付費・訓練等給付費等請求書')

@section('content')
    @foreach($bundles as $bundle)
        @foreach($bundle['invoices'] as $invoice)
            <section class="sheet">
                @include('pdfs.billings.dws-billing-invoice.invoice')
            </section>
        @endforeach

        @foreach($bundle['statements'] as $statement)
            @for($page = 1; $page <= $statement->pages(); $page++)
                <section class="sheet">
                    @include('pdfs.billings.dws-billing-invoice.statement.index')
                </section>
            @endfor
        @endforeach
    @endforeach
@endsection
