@push('css')
    <style>
    .appendix.outer > *:nth-child(1) {
        margin-top: 16px;
    }
    .appendix.outer > *:nth-child(3) {
        margin-top: 4px;
    }
    .appendix.outer > *:nth-child(4) {
        margin-top: 12px;
    }
    .appendix.outer > *:nth-child(6) {
        margin-top: 8px;
    }
    .appendix .font-large {
        font-size: 1.1em;
    }
    .appendix .relative {
        position: relative;
    }
    .appendix .office-to-office {
        position: absolute;
        right: 0;
        top: 8px;
        padding: 1px 4px;
        border: solid 1px #000;
        height: 1.3rem;
    }
    .appendix .underline {
        border-bottom: 1px #000 solid;
    }
    .appendix h1.title {
        font-size: 1.3rem;
        text-align: center;
    }
    .appendix .ml-auto {
        margin-left: auto;
    }
    .appendix .border-right {
        border-right: solid 1px #000;
    }
    .appendix .thick-border-top {
        border-top: solid 2px #000;
    }
    .appendix tr:last-of-type {
        border-bottom: solid 2px #000;
    }
    .appendix tr {
        height: 24px;
    }
    .appendix table:not(.main-table) tbody td {
        padding-right: 4px;
    }
    .appendix .user-table td {
        min-width: 22px;
    }
    .appendix .main-table > tbody > tr:first-of-type {
        border-top: solid 2px #000;
    }
    .appendix .main-table > tbody tr > td:nth-of-type(1),
    .appendix .main-table > tbody tr:not(:last-of-type) > td:nth-of-type(3) {
        text-align: left;
        padding-left: 3px;
    }
    .appendix .main-table > tbody tr:not(:last-of-type) > td:nth-of-type(5) {
        text-align: right;
    }
    .appendix .main-table > tbody tr > td:nth-of-type(1) {
        line-height: 1rem;
    }
    .appendix .main-table > tbody tr:last-of-type > td:nth-of-type(n+2):nth-of-type(-n+4) {
        font-weight: bold;
    }
    .appendix .main-table > tbody tr:not(:last-of-type) {
        min-height: 2rem;
        max-height: 2rem;
        height: 2rem;
    }
    .appendix .main-table tbody td {
        padding-left: 3px;
        padding-right: 3px;
    }
    .appendix .lower-area {
        padding: 0 20px;
    }
    .appendix .lower-area > .left {
        flex: 0 0 58%;
    }
    .appendix .lower-area > .right {
        flex: 0 0 38%;
        padding-bottom: 18px;
    }
    .appendix .lower-area > .left > *:not(:first-child),
    .appendix .lower-area > .right > *:not(:first-child) {
        margin-top: 6px;
    }
    .appendix .limits-table > thead tr > td:nth-of-type(4),
    .appendix .limits-table > tbody tr > td:nth-of-type(4) {
        border-right: solid 2px #000;
    }
    .appendix .lower-area .notes {
        padding-left: 16px;
    }
    .appendix .lower-area .notes > span:nth-of-type(2) {
        padding-left: 0.8rem;
    }
    .appendix .billing-amount .title {
        font-size: 1.15rem;
        font-weight: bold;
        padding-left: 16px;
    }
    </style>
@endpush

<div class="outer flex column appendix">
    <div class="text-center relative font-small">
        <h1 class="title">
            <span class="date">{{ $model->providedIn->toEraName() }}<span class="year">{{ $model->providedIn->toJapaneseYear() }}</span>年<span class="month">{{ $model->providedIn->format('n') }}</span>月分</span>
            サービス提供票別表
        </h1>
        <span class="office-to-office">サービス事業所→居宅介護支援事業所</span>
    </div>
    <div class="flex" style="height: 1.4rem">
        <div style="margin-left: 24px;">区分支給限度管理・利用者負担計算</div>
        <div class="flex ml-auto">
            <span class="underline" style="margin-right: 48px;">被保険者番号：{{ $model->insNumber }}</span>
            <span class="flex justify-between underline" style="width: 260px">利用者：{{ $model->userName }}<span>様</span></span>
        </div>
    </div>
    <table class="thick-border text-center full-width main-table">
        <thead>
            <tr>
                <td rowspan="2" style="width: 8%">事業所名</td>
                <td rowspan="2" style="width: 6%">事業所番号</td>
                <td rowspan="2" style="width: 8%">サービス内容<br>/種類</td>
                <td rowspan="2" style="width: 4.3%">サービス<br>コード</td>
                <td rowspan="2" style="width: 3.6%">単位数</td>
                <td colspan="3" style="width: 6%" class="font-x-small">割引適用後</td>
                <td rowspan="2" style="width: 2.5%">回数</td>
                <td rowspan="2" style="width: 5%" class="font-x-small">サービス<br>単位数<br>/金額</td>
                <td rowspan="2" style="width: 5%" class="font-x-small">給付管理<br>単位数</td>
                <td rowspan="2" style="width: 4.6%" class="font-xx-small">種類支給限度<br>基準を超える<br>単位数</td>
                <td rowspan="2" style="width: 4.6%" class="font-x-small">種類支給限度基準内単位数</td>
                <td rowspan="2" style="width: 4.6%" class="font-xx-small">区分支給限度基準を超える単位数</td>
                <td rowspan="2" style="width: 4.6%" class="font-x-small">区分支給限度基準内単位数</td>
                <td rowspan="2" style="width: 4%" class="font-x-small">単位数<br>単価</td>
                <td rowspan="2" style="width: 5%" class="font-x-small">費用総額<br>（保険/事業対象分）</td>
                <td rowspan="2" style="width: 3.2%" class="font-x-small">給付率<br>(%)</td>
                <td rowspan="2" style="width: 5%" class="font-x-small">保険/事業費<br>請求額</td>
                <td rowspan="2" style="width: 5%" class="font-x-small">定額<br>利用者負担<br>単価金額</td>
                <td rowspan="2" style="width: 5.5%" class="font-x-small">利用者負担<br>（保険/事業<br>対象分）</td>
                <td rowspan="2" style="width: 5.5%" class="font-xx-small">利用者負担<br>（全額負担分）</td>
            </tr>
            <tr class="font-x-small">
                <td>率(％)</td>
                <td colspan="2" class="border-right">単位数</td>
            </tr>
        </thead>
        <tbody class="font-small">
            @foreach($model->entries as $entry)
                <tr>
                    <td class="font-x-small">{{ $entry->officeName }}</td>
                    <td>{{ $entry->officeCode }}</td>
                    <td class="font-x-small">{{ $entry->serviceName }}</td>
                    <td>{{ $entry->serviceCode ?? '' }}</td>
                    <td>{{ $entry->unitScore ?? '' }}</td>
                    <td></td>
                    <td colspan="2"></td>
                    <td class="text-right">{{ $entry->count ?? '' }}</td>
                    <td class="text-right">{{ $entry->wholeScore }}</td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ $entry->maxBenefitQuotaExcessScore ?? '' }}</td>
                    <td class="text-right">{{ $entry->scoreWithinMaxBenefitQuota ?? '' }}</td>
                    <td class="text-right">{{ $entry->maxBenefitExcessScore ?? '' }}</td>
                    <td class="text-right">{{ $entry->scoreWithinMaxBenefit ?? '' }}</td>
                    <td class="text-right">{{ $entry->unitCost ?? '' }}</td>
                    <td class="text-right">{{ $entry->totalFeeForInsuranceOrBusiness ?? '' }}</td>
                    <td class="text-right">{{ $entry->benefitRate ?? '' }}</td>
                    <td class="text-right">{{ $entry->claimAmountForInsuranceOrBusiness ?? '' }}</td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ $entry->copayForInsuranceOrBusiness ?? '' }}</td>
                    <td class="text-right">{{ $entry->copayWholeExpense ?? '' }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < $model->extraEntryRows(); $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="2"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
            <tr>
                <td class="no-use"></td>
                <td colspan="3" class="font-large">区分支給限度基準額（単位）</td>
                <td colspan="4" class="text-right font-large">{{ $model->maxBenefit }}</td>
                <td class="font-large">合計</td>
                <td class="text-right">{{ $model->totalScoreTotal }}</td>
                <td class="text-right"></td>
                <td class="text-right"></td>
                <td class="text-right"></td>
                <td class="text-right">{{ $model->maxBenefitExcessScoreTotal }}</td>
                <td class="text-right">{{ $model->scoreWithinMaxBenefitTotal }}</td>
                <td class="no-use text-right"></td>
                <td class="text-right">{{ $model->totalFeeForInsuranceOrBusinessTotal }}</td>
                <td class="no-use text-right"></td>
                <td class="text-right">{{ $model->claimAmountForInsuranceOrBusinessTotal }}</td>
                <td class="no-use text-right"></td>
                <td class="text-right">{{ $model->copayForInsuranceOrBusinessTotal }}</td>
                <td class="text-right">{{ $model->copayWholeExpenseTotal }}</td>
            </tr>
        </tbody>
    </table>
    <div class="flex justify-between lower-area">
        <div class="left">
            <div style="padding-left: 24px;">種類別支給限度管理</div>
            <table class="thick-border full-width limits-table">
                <tbody class="text-right">
                    <tr class="text-center">
                        <td>サービス種類</td>
                        <td>種類別支給限度<br>基準額（単位）</td>
                        <td>合計単位数</td>
                        <td>種類支給限度基準<br>を超える単位数</td>
                        <td>サービス種類</td>
                        <td>種類支給限度<br>基準額（単位）</td>
                        <td>合計単位数</td>
                        <td>種類支給限度基準<br>を超える単位数</td>
                    </tr>
                    @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="thick-border-top text-left">合計</td>
                        <td colspan="2" class="thick-border-top no-use"></td>
                        <td class="thick-border-top">0</td>
                    </tr>
                </tbody>
            </table>
            <div style="width: 42%;">
                <div>要介護認定期間中の短期入所利用日数</div>
                <table class="thick-border full-width">
                    <tbody>
                        <tr class="text-center">
                            <td>前月までの利用日数</td>
                            <td>当日の計画利用日数</td>
                            <td>累積利用日数</td>
                        </tr>
                        <tr class="text-right">
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex column justify-end right">
            <div class="flex column font-small notes">
                <span>※上記の「区分支給限度管理・利用者負担計算」欄の請求金額は、</span>
                <span>実際の請求金額と異なる場合があります。</span>
                <span>※実際の請求金額は、下記の「請求額の計算」欄に記載しています。</span>
            </div>
            <div class="billing-amount">
                <div class="title">請求額の計算</div>
                <table class="full-width thick-border">
                    <tbody>
                        <tr class="text-center">
                            <td style="width: 24%;">保険請求分</td>
                            <td style="width: 24%;">公費請求額</td>
                            <td style="width: 28%;">社会福祉法人等による<br>利用者負担の減免</td>
                            <td style="width: 24%;">利用者請求額</td>
                        </tr>
                        <tr class="text-right">
                            <td>{{ $model->insuranceClaimAmount }}</td>
                            <td>{{ $model->subsidyClaimAmount }}</td>
                            <td>0</td>
                            <td>{{ $model->copayAmount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
