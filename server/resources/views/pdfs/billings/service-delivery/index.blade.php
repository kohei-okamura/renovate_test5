@extends('pdfs.base-landscape')

@section('title', '介護給付費・訓練等給付費等請求書')

@section('content')
    <section class="sheet">
        <div class="service-delivery">
            @include('pdfs.billings.service-delivery.header')
            @include('pdfs.billings.service-delivery.contract')
            @include('pdfs.billings.service-delivery.item')
        </div>
    </section>
    <section class="sheet">
        <div class="service-delivery">
            @include('pdfs.billings.service-delivery.attached-header')
            @include('pdfs.billings.service-delivery.office')
            @include('pdfs.billings.service-delivery.manage')
        </div>
    </section>
@endsection

@push('css')
    <style>
        .service-delivery {
            font-size: 11px;
            padding-left: 5mm;
            padding-right: 5mm;
        }
        table {
            width: 100%;
        }
        table th, table td {
            border: 1px solid black;
            text-align: center;
            vertical-align: middle;
        }

        .white-space-nowrap {
            white-space: nowrap;
        }

        .font-size-8px {
            font-size: 10px;
            transform: scale(0.8);
        }
        .font-size-10px {
            font-size: 10px;
        }
        .font-size-12px {
            font-size: 12px;
        }
        .font-size-14px {
            font-size: 14px;
        }
        .font-size-16px {
            font-size: 16px;
        }
        .font-size-18px {
            font-size: 18px;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .block-right {
            margin-left: auto;
            margin-right: 0;
        }

        .float-left {
            float: left;
        }
        .float-right {
            float: right;
        }

        .margin-left-5mm {
            margin-left: 5mm;
        }
        .margin-left-100mm {
            margin-left: 100mm;
        }
        .margin-right-2mm {
            margin-right: 2mm;
        }
        .margin-right-7mm {
            margin-right: 7mm;
        }
        .margin-right-50mm {
            margin-right: 50mm;
        }
        .margin-top-2mm {
            margin-top: 2mm;
        }
        .margin-top-7mm {
            margin-top: 7mm;
        }
        .margin-top-15mm {
            margin-top: 15mm;
        }
        .margin-top-50mm {
            margin-top: 50mm;
        }
        .margin-bottom-1mm {
            margin-bottom: 1mm;
        }
        .margin-bottom-3mm {
            margin-bottom: 3mm;
        }

        .padding-1mm {
            padding: 1mm;
        }
        .padding-2mm {
            padding: 2mm;
        }
        .padding-left-10mm {
            padding-left: 10mm;
        }

        .text-align-left {
            padding-left: 1mm;
            text-align: left;
        }
        .text-align-center {
            text-align: center;
        }
        .text-align-right {
            padding-right: 1mm;
            text-align: right;
        }
        .vertical-align-top {
            vertical-align: top;
        }
        .vertical-align-middle {
            vertical-align: middle;
        }

        .border-left-none {
            border-left: none;
        }
        .border-right-none {
            border-right: none;
        }
        .border {
            border: 1px solid black;
        }
        .border-none {
            border: none;
        }
        .border-white {
            border: 1px solid white;
        }
        .border-bold {
            border: 2px solid black;
        }
        .border-left-bold {
            border-left: 2px solid black;
        }
        .border-right-bold {
            border-right: 2px solid black;
        }
        .border-bottom {
            border-bottom: 1px solid black;
        }
        .border-bottom-bold {
            border-bottom: 2px solid black;
        }

        .width-auto {
            width: auto;
        }
        .width-60p {
            width: 60%;
        }

        .width-5mm {
            width: 5mm;
        }
        .width-7mm {
            width: 7mm;
        }
        .width-10mm {
            width: 10mm;
        }
        .width-14mm {
            width: 14mm;
        }
        .width-15mm {
            width: 15mm;
        }
        .width-18mm {
            width: 18mm;
        }
        .width-20mm {
            width: 20mm;
        }
        .width-22mm {
            width: 22mm;
        }
        .width-23mm {
            width: 23mm;
        }
        .width-25mm {
            width: 25mm;
        }
        .width-30mm {
            width: 30mm;
        }
        .width-35mm {
            width: 35mm;
        }
        .width-40mm {
            width: 40mm;
        }
        .width-55mm {
            width: 55mm;
        }
        .width-60mm {
            width: 60mm;
        }
        .width-63mm {
            width: 63mm;
        }
        .width-70mm {
            width: 70mm;
        }
        .width-140mm {
            width: 140mm;
        }
        .height-4mm {
            height: 4mm;
        }
        .height-5mm {
            height: 5mm;
        }
        .height-6mm {
            height: 6mm;
        }
        .height-10mm {
            height: 10mm;
        }
        .height-12mm {
            height: 12mm;
        }
    </style>
@endpush
