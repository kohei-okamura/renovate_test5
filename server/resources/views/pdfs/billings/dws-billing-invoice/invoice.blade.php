<?php
/** @var \Domain\Billing\DwsBillingInvoicePdf $invoice */
?>

@push('css')
    <style>
    .invoice.outer > *:nth-child(n+3) {
        margin-top: 12px;
    }
    .invoice .flex.justify-start {
        justify-content: flex-start;
    }
    .invoice .font-table {
        font-size: 0.9rem;
    }
    .invoice .lattice > *:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    .invoice .text-nowrap {
        white-space: nowrap;
    }
    .invoice .first-block {
        margin-top: 36px;
    }
    .invoice .user-block {
        padding: 24px 42px 24px 0;
    }
    .invoice .office-block > *:not(:first-of-type) {
        margin-top: 48px;
    }
    .invoice .office-table td {
        padding: 0 6px;
    }
    .invoice .office-table td:first-of-type {
        text-align: center;
    }
    .invoice .office-table td:not(:first-of-type) {
        border-left: solid 2px #000;
    }
    .invoice .office-table tr:not(:last-of-type) {
        border-bottom: solid 2px #000;
    }
    .invoice .office-number > td:first-of-type {
        border-right: solid 2px #000;
    }
    .invoice .office-number > td:nth-child(n+4) {
        border-left: dashed 1px #000;
    }
    .invoice .target-date,
    .invoice .amount-billed {
        font-size: 0.9rem;
        height: 48px;
    }
    .invoice .target-date > span,
    .invoice .amount-billed > span {
        align-items: center;
        display: inline-flex;
        justify-content: center;
    }
    .invoice .target-date > span:nth-child(3n + 1) {
        padding: 0 18px;
    }
    .invoice .target-date > span:not(:nth-child(3n + 1)) {
        min-width: 22px;
    }
    .invoice .amount-billed > span:first-of-type {
        padding: 0 18px;
    }
    .invoice .amount-billed > span:not(:first-of-type) {
        min-width: 48px;
    }
    .invoice .amount-billed .number {
        align-items: flex-end;
        display: inline-flex;
        padding-bottom: 2px;
    }
    .invoice .main-table {
        flex: 1;
        width: 100%;
    }
    .invoice .main-table th {
        font-weight: normal;
    }
    .invoice .main-table th:nth-of-type(n+2) {
        width: 12.5%;
    }
    .invoice .main-table tr {
        height: 45px;
    }
    .invoice .main-table td:not(.no-pad) {
        padding: 0 6px;
    }
    .invoice .main-table thead > tr {
        border-bottom: solid 2px #000;
    }
    .invoice .main-table tbody > tr:not(:last-of-type) {
        border-bottom: solid 1px #000;
    }
    .invoice .main-table tbody > tr:nth-of-type(14) {
        height: 2px;
    }
    .invoice .main-table td:not(:last-of-type),
    .invoice .main-table th:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    .invoice .main-table td:nth-last-of-type(-n+6) {
        text-align: right;
    }
    </style>
@endpush

<div class="format-block">(様式第一)</div>
<div class="invoice outer flex column">
    <h1 class="title">介護給付費・訓練等給付費等請求書</h1>
    <div class="flex first-block">
        <div class="user-block flex flexible column justify-between">
            <div>( 請 求 先 )</div>
            <div class="flex"><span class="flexible text-center">{{ $invoice->destinationName }}</span>殿</div>
            <div>下記のとおり請求します。</div>
        </div>
        <div class="office-block flex flexible column justify-between">
            <div class="text-center">{{ $invoice->issuedOn }}</div>
            <div class="flex justify-end">
                <table class="thick-border office-table">
                    <tbody>
                        <tr class="font-small text-center office-number" style="height: 24px">
                            <td rowspan="5"><span class="font-table">請<br>求<br>事<br>業<br>者</span></td>
                            <td class="text-nowrap">指定事業所番号</td>
                            <td>{{ mb_substr($invoice->office->code, 0, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 1, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 2, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 3, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 4, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 5, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 6, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 7, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 8, 1) }}</td>
                            <td>{{ mb_substr($invoice->office->code, 9, 1) }}</td>
                        </tr>
                        <tr style="height: 100px">
                            <td>住 所<br>(所在地)</td>
                            <td colspan="10" class="font-small" style="vertical-align: baseline;">〒 {{ $invoice->office->addr->postcode }}<br>{{ \Domain\Common\Prefecture::resolve($invoice->office->addr->prefecture) . $invoice->office->addr->city . $invoice->office->addr->street . $invoice->office->addr->apartment}}</td>
                        </tr>
                        <tr style="height: 24px">
                            <td>電話番号</td>
                            <td colspan="10" class="font-small">{{ $invoice->office->tel }}</td>
                        </tr>
                        <tr style="height: 68px">
                            <td>名 称</td>
                            <td colspan="10" class="font-small">{{ $invoice->office->name }}</td>
                        </tr>
                        <tr style="height: 40px">
                            <td>職・氏名</td>
                            <td colspan="10" class="font-small"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="flex">
        <div class="flex thick-border lattice target-date">
            <span>{{ $invoice->providedIn['japaneseCalender'] }}</span>
            <span>{{ mb_substr($invoice->providedIn['year'], 0, 1) }}</span>
            <span>{{ mb_substr($invoice->providedIn['year'], 1, 1) }}</span>
            <span>年</span>
            <span>{{ mb_substr($invoice->providedIn['month'], 0, 1) }}</span>
            <span>{{ mb_substr($invoice->providedIn['month'], 1, 1) }}</span>
            <span>月分</span>
        </div>
    </div>
    <div class="flex">
        <div class="flex thick-border lattice amount-billed">
            <span>請求金額</span>
            <span class="number">{{ $invoice->claimAmount[1] }}</span>
            <span class="number">{{ $invoice->claimAmount[2] }}</span>
            <span class="flex column justify-start">
                <span class="font-xx-small">百万</span>
                <span class="flexible number">{{ $invoice->claimAmount[3] }}</span>
            </span>
            <span class="number">{{ $invoice->claimAmount[4] }}</span>
            <span class="number">{{ $invoice->claimAmount[5] }}</span>
            <span class="flex column justify-start">
                <span class="font-xx-small">千</span>
                <span class="flexible number">{{ $invoice->claimAmount[6] }}</span>
            </span>
            <span class="number">{{ $invoice->claimAmount[7] }}</span>
            <span class="number">{{ $invoice->claimAmount[8] }}</span>
            <span class="flex column justify-start">
                <span class="font-xx-small">円</span>
                <span class="flexible number">{{ $invoice->claimAmount[9] }}</span>
            </span>
        </div>
    </div>
    <table class="thick-border main-table">
        <thead>
            <tr>
                <th colspan="2" scope="col">区分</th>
                <th scope="col">件数</th>
                <th scope="col">単位数</th>
                <th scope="col">費用合計</th>
                <th scope="col">給付費<br>請求額</th>
                <th scope="col">利用者<br>負担額</th>
                <th scope="col">自治体<br>助成額</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < 7; $i++)
                <tr>
                    @if($i === 0)
                        <td rowspan="7" class="no-pad text-center thin-width"><span>介<br>護<br>給<br>付<br>費</span></td>
                    @endif
                    <td>{{ empty($invoice->items[$i]) ? '' : \Domain\Billing\DwsServiceDivisionCode::resolve($invoice->items[$i]->serviceDivisionCode) }}</td>
                    <td>{{ empty($invoice->items[$i]) ? '' : number_format($invoice->items[$i]->subtotalCount) }}</td>
                    <td>{{ empty($invoice->items[$i]) ? '' : number_format($invoice->items[$i]->subtotalScore) }}</td>
                    <td>{{ empty($invoice->items[$i]) ? '' : number_format($invoice->items[$i]->subtotalFee) }}</td>
                    <td>{{ empty($invoice->items[$i]) ? '' : number_format($invoice->items[$i]->subtotalBenefit) }}</td>
                    <td>{{ empty($invoice->items[$i]) ? '' : number_format($invoice->items[$i]->subtotalCopay) }}</td>
                    <td>{{ empty($invoice->items[$i]) ? '' : number_format($invoice->items[$i]->subtotalSubsidy) }}</td>
                </tr>
            @endfor
            @for($i = 0; $i < 4; $i++)
                <tr>
                    @if($i === 0)
                        <td rowspan="4" class="no-pad text-center thin-width"><span>訓<br>練<br>等<br>給<br>付<br>費</span></td>
                    @endif
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
            @for($i = 0; $i < 2; $i++)
                <tr>
                    @if($i === 0)
                        <td rowspan="2" class="no-pad">
                            <div class="flex justify-center" style="font-size: 0.7rem; padding: 0 3px; gap: 3px;">
                                <div>地域相談</div>
                                <div>支援給付費</div>
                            </div>
                        </td>
                    @endif
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-pad no-use"></td>
                    <td></td>
                </tr>
            @endfor
            <tr><td colspan="8"></td></tr>
            <tr>
                <td colspan="2" class="text-center">小 計</td>
                <td>{{ number_format($invoice->dwsPayment->subtotalDetailCount) }}</td>
                <td>{{ number_format($invoice->dwsPayment->subtotalScore) }}</td>
                <td>{{ number_format($invoice->dwsPayment->subtotalFee) }}</td>
                <td>{{ number_format($invoice->dwsPayment->subtotalBenefit) }}</td>
                <td>{{ number_format($invoice->dwsPayment->subtotalCopay) }}</td>
                <td>{{ number_format($invoice->dwsPayment->subtotalSubsidy) }}</td>
            </tr>
            <tr class="thick-border-top">
                <td colspan="2" class="text-center">特定障害者特別給付費</td>
                <td>{{ number_format($invoice->highCostDwsPayment->subtotalDetailCount) }}</td>
                <td class="no-pad no-use"></td>
                <td>{{ number_format($invoice->highCostDwsPayment->subtotalFee) }}</td>
                <td>{{ number_format($invoice->highCostDwsPayment->subtotalBenefit) }}</td>
                <td class="no-pad no-use"></td>
                <td></td>
            </tr>
            <tr class="thick-border-top">
                <td colspan="2" class="text-center">合 計</td>
                <td>{{ number_format($invoice->totalCount) }}</td>
                <td>{{ number_format($invoice->totalScore) }}</td>
                <td>{{ number_format($invoice->totalFee) }}</td>
                <td>{{ number_format($invoice->totalBenefit) }}</td>
                <td>{{ number_format($invoice->totalCopay) }}</td>
                <td>{{ number_format($invoice->totalSubsidy) }}</td>
            </tr>
        </tbody>
    </table>
</div>
