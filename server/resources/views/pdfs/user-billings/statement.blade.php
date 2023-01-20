@extends('pdfs.base')

<?php
/**
 * @var \Domain\UserBilling\UserBillingStatementPdf $billing
 */
?>

@push('css')
    <style>
    .statement.outer {
        padding: 15mm 20mm;
    }
    .statement.outer > *:nth-child(n+4):not(:last-child) {
        margin-top: 24px;
    }
    .statement.outer > *:last-child {
        margin-top: 30px;
    }
    .statement .title {
        font-size: 24pt;
        font-weight: normal;
        margin: 42px 0;
        text-align: center;
    }
    .statement table {
        border-collapse: collapse;
        border-spacing: 0;
        font-size: 11pt;
    }
    .statement thead tr {
        height: 30px;
    }
    .statement tbody tr {
        height: 26px;
    }
    .statement .destination > div:nth-of-type(2) {
        margin-top: 12px;
    }
    .statement .publisher > div:nth-of-type(2) {
        margin-top: 18px;
    }
    .statement .destination > div:nth-of-type(3),
    .statement .publisher > div:nth-of-type(3) {
        margin-top: 10px;
    }
    .statement .publisher > div:first-of-type {
        text-align: right;
    }
    .statement .details-table {
        border: solid 1px #434343;
        margin-top: 6px;
    }
    .statement .details-table > tbody td:not(:last-of-type) {
        border-right: solid 1px #434343;
    }
    .statement .details-table > thead > tr {
        background-color: #434343;
        color: #fff;
    }
    .statement .details-table td > span > span,
    .statement .totalling-table td span {
        margin-right: 6px;
    }
    .statement .details-table td:first-of-type {
        text-align: center;
    }
    .statement .details-table td:nth-of-type(n+3),
    .statement .totalling-table td:not(:first-of-type) {
        text-align: right;
    }
    .statement .totalling-table tr {
        border-bottom: solid 1px #434343;
    }
    .statement .details-table td,
    .statement .totalling-table td {
        padding: 0 4px;
    }
    .statement .totalling-table {
        width: 32%;
    }
    .statement .totalling-table td {
        width: 50%;
    }
    .statement .period > span {
        margin: 0 4px;
    }
    .statement .page span {
        margin: 0 6px;
    }
    /* レイアウト崩れ回避 */
    .statement .details-table tr:not(:last-of-type) {
        border-bottom: solid 1px transparent;
    }
    </style>
@endpush

@section('title', '利用者請求：介護サービス利用明細書')

@section('content')
    @foreach($billings as $billing)
        <section class="sheet gothic statement outer">
            @include('pdfs.user-billings.header', [
                'addr' => $billing->user->addr,
                'corporationName' => '',
                'issuedOn' => $billing->issuedOn,
                'destinationName' => $billing->user->name->displayName,
                'office' => $billing->office]
            )
            <h1 class="title">介護サービス利用明細書</h1>
            <section class="flex column align-end details-section">
                <span class="period">期間:<span>{{ $billing->period->start->toJapaneseDate() }}</span>〜<span>{{ $billing->period->end->toJapaneseDate() }}</span></span>
                <table class="full-width details-table">
                    <thead>
                        <tr>
                            <td style="width: 16%;">サービスコード</td>
                            <td>サービス内容</td>
                            <td style="width: 16%;">単価</td>
                            <td style="width: 16%;">数量</td>
                            <td style="width: 16%;">小計</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($billing->billingItems as $billingItem)
                            <tr>
                                <td>{{ $billingItem->serviceCode }}</td>
                                <td>{{ $billingItem->serviceName }}</td>
                                <td><span><span>{{ $billingItem->unitScore }}</span>単位</span></td>
                                <td>{{ $billingItem->count }}</td>
                                <td><span><span>{{ $billingItem->totalScore }}</span>単位</span></td>
                            </tr>
                        @endforeach
                        @for($i = 0; $i < 25 - count($billing->billingItems); $i++)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </section>
            <section class="flex justify-end totalling-section">
                <table class="totalling-table">
                    <tbody>
                        <tr>
                            <td>合計単位数</td>
                            <td><span>{{ $billing->itemsAmounts->score }}</span>単位</td>
                        </tr>
                        <tr>
                            <td>単位数単価</td>
                            <td><span>{{ $billing->itemsAmounts->unitCost }}</span>円</td>
                        </tr>
                        <tr>
                            <td>合計金額</td>
                            <td><span>{{ $billing->itemsAmounts->subtotalCost }}</span>円</td>
                        </tr>
                        <tr>
                            <td>介護給付額</td>
                            <td><span>{{ $billing->itemsAmounts->benefitAmount }}</span>円</td>
                        </tr>
                        <tr>
                            <td>利用者負担額</td>
                            <td><span>{{ $billing->itemsAmounts->copayWithTax }}</span>円</td>
                        </tr>
                    </tbody>
                </table>
            </section>
            <section class="flex justify-end page-section">
                <span class="page"><span>{{ $billing->page }}</span>/<span>{{ $billing->maxPage }}</span>ページ</span>
            </section>
        </section>
    @endforeach
@endsection
