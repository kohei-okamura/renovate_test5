@extends('pdfs.base')

@push('css')
    <style>
    .notice.outer {
        padding: 15mm 20mm;
    }
    .notice .full-width {
        width: 100%;
    }
    .notice .font-x-large {
        font-size: 20pt;
    }
    .notice .font-normal {
        font-size: 12pt;
    }
    .notice.outer > *:nth-child(n+4):not(:last-child) {
        margin-top: 24px;
    }
    .notice.outer > *:last-child {
        margin-top: 30px;
    }
    .notice .title {
        font-size: 24pt;
        font-weight: normal;
        margin: 42px 0;
        text-align: center;
    }
    .notice table {
        border-collapse: collapse;
        border-spacing: 0;
        font-size: 13pt;
    }
    .notice thead tr {
        height: 30px;
    }
    .notice tbody tr {
        height: 26px;
    }
    .notice .destination > div:nth-of-type(2) {
        margin-top: 12px;
    }
    .notice .publisher > div:nth-of-type(2) {
        margin-top: 18px;
    }
    .notice .destination > div:nth-of-type(3),
    .notice .publisher > div:nth-of-type(3) {
        margin-top: 10px;
    }
    .notice .publisher > div:first-of-type {
        text-align: right;
    }
    .notice .details-table {
        border: solid 1px #434343;
        margin-top: 6px;
    }
    .notice .details-table > tbody td:last-of-type {
        width: 60%;
        padding-left: 20px;
    }
    .notice .details-table > tbody td:not(:last-of-type) {
        border-right: solid 1px #434343;
    }
    .notice .details-table > thead > tr {
        background-color: #434343;
        color: #fff;
    }
    .notice .details-table tr {
        border-bottom: solid 1px #434343;
    }
    .notice .details-table td > span > span {
        margin-right: 6px;
    }
    .notice .details-table td {
        padding: 10px 4px;
    }
    .notice .breakdown {
        padding: 10px !important;
    }
    .notice .amount {
        padding-top: 16px !important;
        padding-bottom: 16px !important;
    }
    .notice .amount span {
        margin-left: 4px;
    }
    .notice .page span {
        margin: 0 6px;
    }
    </style>
@endpush

@section('title', '??????????????????????????????????????????')

@section('content')
    @foreach($notices as $notice)
        <section class="sheet gothic notice outer">
            @include('pdfs.user-billings.header', [
                'addr' => $notice->userAddr,
                'corporationName' => '',
                'issuedOn' => $notice->issuedOn,
                'destinationName' => $notice->dwsBillingUser->name->displayName,
                'office' => $notice->office
            ])
            <h1 class="title">????????????????????????</h1>
            <div class="text-center font-normal">????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</div>
            <section class="flex column align-end details-section">
                <table class="full-width details-table">
                    <tbody>
                    <tr>
                        <td colspan="2">??????????????????</td>
                        <td>{{ $notice->dwsNumber }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">??????????????????????????????</td>
                        <td>{{ $notice->dwsBillingUser->name->displayName }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">????????????????????????????????????</td>
                        <td>{{ $notice->dwsBillingUser->childName->displayName }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">????????????????????????</td>
                        <td>{{ $notice->providedIn }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">????????????</td>
                        <td>{{ $notice->cityName }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">????????????????????????</td>
                        <td>{{ $notice->dwsServiceDivision }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">?????????</td>
                        <td>{{ $notice->issuedOn }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">????????????</td>
                        <td class="amount font-x-large">{{ number_format($notice->receiptedAmount) }} ??? <span class="font-normal">(??? ??? ???)</span></td>
                    </tr>
                    <tr>
                        <td rowspan="2" class="breakdown text-center" style="width: 35pt">??????</td>
                        <td>?????????????????????????????? ???</td>
                        <td>{{ number_format($notice->subtotalFee) }} ???</td>
                    </tr>
                    <tr>
                        <td>?????????????????? ???</td>
                        <td>{{ number_format($notice->subtotalCopay) }} ???</td>
                    </tr>
                    </tbody>
                </table>
            </section>
        </section>
    @endforeach
@endsection
