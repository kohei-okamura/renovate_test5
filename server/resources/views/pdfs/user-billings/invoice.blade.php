@extends('pdfs.base')

@push('css')
    <style>
    .invoice.outer {
        padding: 15mm 20mm;
    }
    .invoice .full-width {
        width: 100%;
    }
    .invoice .font-x-large {
        font-size: 24pt;
    }
    .invoice .font-normal {
        font-size: 12pt;
    }
    .invoice.outer > *:nth-child(n+4) {
        margin-top: 24px;
    }
    .invoice .title {
        font-size: 24pt;
        font-weight: normal;
        margin: 42px 0;
        text-align: center;
    }
    .invoice table {
        border-collapse: collapse;
        border-spacing: 0;
        font-size: 11pt;
    }
    .invoice tr {
        height: 30px;
    }
    .invoice .destination > div:nth-of-type(2) {
        margin-top: 12px;
    }
    .invoice .publisher > div:nth-of-type(2) {
        margin-top: 18px;
    }
    .invoice .destination > div:nth-of-type(3),
    .invoice .publisher > div:nth-of-type(3) {
        margin-top: 10px;
    }
    .invoice .publisher > div:first-of-type {
        text-align: right;
    }
    .invoice .summary-table td:first-of-type,
    .invoice .details-table > thead > tr {
        background-color: #434343;
        color: #fff;
    }
    .invoice .summary-table tr {
        border-bottom: solid 1px #fff;
    }
    .invoice .summary-table td:first-of-type {
        text-align: center;
    }
    .invoice .summary-table td:not(:first-of-type) {
        padding-left: 16px;
    }
    .invoice .summary-table td span {
        margin-right: 8px;
    }
    .invoice .details-table td > span > span,
    .invoice .totalling-table td span {
        margin-right: 6px;
    }
    .invoice .details-table td:not(:first-of-type),
    .invoice .totalling-table td:not(:first-of-type) {
        text-align: right;
    }
    .invoice .totalling-table tr:nth-last-of-type(2) {
        border: 0;
    }
    .invoice .details-table tr,
    .invoice .totalling-table tr:not(:nth-last-of-type(2)) {
        border-bottom: solid 1px #434343;
    }
    .invoice .details-table td,
    .invoice .totalling-table td {
        padding: 0 4px;
    }
    .invoice .totalling-table td {
        width: 50%;
    }
    .invoice .period > span {
        margin: 0 4px;
    }
    .invoice .summary-table {
        margin-top: 20px;
    }
    </style>
    @stack('css')
@endpush

@section('title', '???????????????????????????')

@section('content')
    @foreach($billings as $billing)
        <section class="sheet gothic invoice outer">
            @include('pdfs.user-billings.header', [
                'addr' => $billing->billingDestination->addr,
                'corporationName' => $billing->billingDestination->corporationName,
                'issuedOn' => $billing->issuedOn,
                'destinationName' => $billing->billingDestination->agentName,
                'office' => $billing->office
            ])
            <h1 class="title">?????????</h1>
            <section class="summary-section">
                <p>????????????????????????????????????????????????</p>
                <table class="full-width summary-table">
                    <tbody>
                        <tr>
                            <td style="width: 18%;">??????</td>
                            <td class="font-normal"><span>{{ $billing->user->name->displayName }}</span><span>???</span><span>???????????????????????????</span><span>{{ $billing->providedIn }}???</span></td>
                        </tr>
                        <tr>
                            <td>????????????</td>
                            <td><span class="font-x-large"><span>{{ number_format($billing->totalAmount) }}</span>???</span><span>(??????)</span></td>
                        </tr>
                        <tr>
                            @if($billing->user->billingDestination->paymentMethod === \Domain\User\PaymentMethod::withdrawal())
                                <td>???????????????</td>
                                <td>{{ $billing->deductedOn }}</td>
                            @else
                                <td>???????????????</td>
                                <td>{{ $billing->dueDate }}</td>
                            @endif
                        </tr>
                        @if($billing->user->billingDestination->paymentMethod === \Domain\User\PaymentMethod::transfer())
                            <tr>
                                <td>????????????</td>
                                <td>???????????????????????????????????????????????? 1151519<br>?????????????????????????????????????????????</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </section>
            <section class="flex column align-end details-section">
                @include('pdfs.user-billings.common-details-table', ['billing' => $billing])
            </section>
            <section class="flex justify-end totalling-section">
                @include('pdfs.user-billings.common-totalling-table', ['billing' => $billing])
            </section>
        </section>
    @endforeach
@endsection
