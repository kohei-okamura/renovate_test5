<?php
/**
 * @var \Domain\UserBilling\UserBillingPaymentPdf $billing
 */
?>

@push('css')
    <style>
    .details-table {
        margin-top: 6px;
    }
    </style>
@endpush

<span class="period">期間:<span>{{ $billing->period->start->toJapaneseDate() }}</span>〜<span>{{ $billing->period->end->toJapaneseDate() }}</span></span>
<table class="full-width details-table">
    <thead>
        <tr>
            <td>摘要</td>
            <td style="width: 20%;">数量</td>
            <td style="width: 12%;">単価</td>
            <td style="width: 16%;">小計</td>
            <td style="width: 16%;">金額</td>
        </tr>
    </thead>
    <tbody>
        @if($billing->dwsItem)
            <tr>
                <td>障害福祉サービス</td>
                <td><span><span>{{ number_format($billing->dwsItem->score) }}</span>単位</span></td>
                <td><span><span>{{ sprintf('%.2f', $billing->dwsItem->unitCost->toInt(2) / 100) }}</span>円</span></td>
                <td><span><span>{{ number_format($billing->dwsItem->subtotalCost) }}</span>円</span></td>
                <td><span><span></span></span></td>
            </tr>
            <tr>
                <td>障害福祉サービス 介護給付額</td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span>{{ 0 > $billing->dwsItem->benefitAmount ? '▲' : '' }}{{ number_format($billing->dwsItem->benefitAmount) }}</span>円</span></td>
                <td><span><span></span></span></td>
            </tr>
            @if($billing->dwsItem->subsidyAmount !== 0)
                <tr>
                    <td>障害福祉サービス 自治体助成額</td>
                    <td><span><span></span></span></td>
                    <td><span><span></span></span></td>
                    <td><span><span>{{ 0 > $billing->dwsItem->subsidyAmount ? '▲' : '' }}{{ number_format($billing->dwsItem->subsidyAmount) }}</span>円</span></td>
                    <td><span><span></span></span></td>
                </tr>
            @endif
            <tr>
                <td>障害福祉サービス 自己負担額</td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span>{{ number_format($billing->dwsItem->copayWithTax) }}</span>円</span></td>
            </tr>
        @endif
        @if($billing->ltcsItem)
            <tr>
                <td>介護保険サービス</td>
                <td><span><span>{{ number_format($billing->ltcsItem->score) }}</span>単位</span></td>
                <td><span><span>{{ sprintf('%.2f', $billing->ltcsItem->unitCost->toInt(2) / 100) }}</span>円</span></td>
                <td><span><span>{{ number_format($billing->ltcsItem->subtotalCost) }}</span>円</span></td>
                <td><span><span></span></span></td>
            </tr>
            <tr>
                <td>介護保険サービス 介護給付額</td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span>{{ 0 > $billing->ltcsItem->benefitAmount ? '▲' : '' }}{{ number_format(abs($billing->ltcsItem->benefitAmount)) }}</span>円</span></td>
                <td><span><span></span></span></td>
            </tr>
            @if($billing->ltcsItem->subsidyAmount !== 0)
                <tr>
                    <td>介護保険サービス 公費負担額</td>
                    <td><span><span></span></span></td>
                    <td><span><span></span></span></td>
                    <td><span><span>{{ 0 > $billing->ltcsItem->subsidyAmount ? '▲' : '' }}{{ number_format(abs($billing->ltcsItem->subsidyAmount)) }}</span>円</span></td>
                    <td><span><span></span></span></td>
                </tr>
            @endif
            <tr>
                <td>介護保険サービス 自己負担額</td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span>{{ number_format($billing->ltcsItem->copayWithTax) }}</span>円</span></td>
            </tr>
        @endif
        @if($billing->otherItemsTotalAmount > 0)
            <tr>
                <td>自己負担サービス</td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span></span></span></td>
                <td><span><span>{{ number_format($billing->otherItemsTotalAmount) }}</span>円</span></td>
            </tr>
        @endif
    </tbody>
</table>
