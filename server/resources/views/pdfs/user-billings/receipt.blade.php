@extends('pdfs.base')

@push('css')
    <style>
    .receipt.outer {
        padding: 15mm 20mm;
    }
    .receipt .font-x-large {
        font-size: 24pt;
    }
    .receipt .font-normal {
        font-size: 12pt;
    }
    .receipt.outer > *:nth-child(n+4) {
        margin-top: 24px;
    }
    .receipt .title {
        font-size: 24pt;
        font-weight: normal;
        margin: 42px 0;
        text-align: center;
    }
    .receipt table {
        border-collapse: collapse;
        border-spacing: 0;
        font-size: 11pt;
    }
    .receipt tr {
        height: 30px;
    }
    .receipt .destination > div:nth-of-type(2) {
        margin-top: 12px;
    }
    .receipt .publisher > div:nth-of-type(2) {
        margin-top: 18px;
    }
    .receipt .destination > div:nth-of-type(3),
    .receipt .publisher > div:nth-of-type(3) {
        margin-top: 10px;
    }
    .receipt .publisher > div:first-of-type {
        text-align: right;
    }
    .receipt .summary-table td:first-of-type,
    .receipt .details-table > thead > tr {
        background-color: #434343;
        color: #fff;
    }
    .receipt .summary-table tr {
        border-bottom: solid 1px #fff;
    }
    .receipt .summary-table td:first-of-type {
        text-align: center;
    }
    .receipt .summary-table td:not(:first-of-type) {
        padding-left: 16px;
    }
    .receipt .summary-table td span {
        margin-right: 8px;
    }
    .receipt .details-table td > span > span,
    .receipt .totalling-table td span {
        margin-right: 6px;
    }
    .receipt .details-table td:not(:first-of-type),
    .receipt .totalling-table td:not(:first-of-type) {
        text-align: right;
    }
    .receipt .totalling-table tr:nth-last-of-type(2) {
        border: 0;
    }
    .receipt .details-table tr,
    .receipt .totalling-table tr:not(:nth-last-of-type(2)) {
        border-bottom: solid 1px #434343;
    }
    .receipt .details-table td,
    .receipt .totalling-table td {
        padding: 0 4px;
    }
    .receipt .totalling-table td {
        width: 50%;
    }
    .receipt .period > span {
        margin: 0 4px;
    }
    .receipt .summary-table {
        margin-top: 20px;
    }
    .receipt .stamp {
        border: solid 1px #434343;
        height: 120px;
        width: 120px;
    }
    .receipt .stamp > span {
        border-bottom: solid 1px #434343;
        display: inline-block;
        text-align: center;
        width: 100%;
    }
    </style>
@endpush

@section('title', '利用者請求：領収書')

@section('content')
    @foreach($billings as $billing)
        <section class="sheet gothic receipt outer">
            @include('pdfs.user-billings.header', [
                'addr' => $billing->billingDestination->addr,
                'corporationName' => $billing->billingDestination->corporationName,
                'issuedOn' => $billing->issuedOn,
                'destinationName' => $billing->billingDestination->agentName,
                'office' => $billing->office
            ])
            <h1 class="title">領収書</h1>
            <section class="summary-section">
                <table class="full-width summary-table">
                    <tbody>
                        <tr>
                            <td style="width: 18%;">件名</td>
                            <td class="font-normal"><span>{{ $billing->user->name->displayName }}</span><span>様</span><span>介護サービス利用料</span><span>{{ $billing->providedIn }}分</span></td>
                        </tr>
                        <tr>
                            <td>合計金額</td>
                            <td><span class="font-x-large"><span>{{ number_format($billing->totalAmount) }}</span>円</span><span>(税込)</span></td>
                        </tr>
                        <tr>
                            <td>領収日</td>
                            <td>{{ $billing->depositedAt }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <section class="flex column align-end details-section">
                @include('pdfs.user-billings.common-details-table', ['billing' => $billing])
            </section>
            <section class="flex justify-between totalling-section">
                <span>上記の金額正に領収いたしました</span>
                <div>
                    <div class="stamp">
                        <span>領収印</span>
                    </div>
                </div>
                @include('pdfs.user-billings.common-totalling-table', ['billing' => $billing])
            </section>
        </section>
    @endforeach
@endsection
