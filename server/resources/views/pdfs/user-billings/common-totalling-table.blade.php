@push('css')
    <style>
    .totalling-table {
        width: 32%;
    }
    </style>
@endpush

<table class="totalling-table">
    <tbody>
        <tr>
            <td>税抜金額(10%)</td>
            <td><span>{{ number_format($billing->normalTaxRate['withoutTax']) }}</span>円</td>
        </tr>
        <tr>
            <td>消費税額(10%)</td>
            <td><span>{{ number_format($billing->normalTaxRate['tax']) }}</span>円</td>
        </tr>
        <tr>
            <td>税抜金額(8%)</td>
            <td><span>{{ number_format($billing->reducedTaxRate['withoutTax']) }}</span>円</td>
        </tr>
        <tr>
            <td>消費税額(8%)</td>
            <td><span>{{ number_format($billing->reducedTaxRate['tax']) }}</span>円</td>
        </tr>
        <tr>
            <td>繰越金額</td>
            <td><span>{{ 0 > $billing->carriedOverAmount ? '▲' : '' }}{{ number_format(abs($billing->carriedOverAmount)) }}</span>円</td>
        </tr>
        <tr>
            <td>合計</td>
            <td><span>{{ number_format($billing->totalAmount) }}</span>円</td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td>医療費控除対象額</td>
            <td><span>{{ number_format($billing->medicalDeductionAmount) }}</span>円</td>
        </tr>
    </tbody>
</table>
