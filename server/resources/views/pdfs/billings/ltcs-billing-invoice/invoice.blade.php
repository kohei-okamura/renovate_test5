@push('css')
    <style>
    .ltcs-billing-invoice.format-block {
        margin-bottom: 4px;
        margin-left: 10px;
    }
    .ltcs-billing-invoice.outer {
        height: 100%;
        padding: 14px 12px 14px 18px;
        width: 100%;
    }
    .ltcs-billing-invoice.outer > *:nth-child(2) {
        margin-top: 18px;
    }
    .ltcs-billing-invoice.outer > *:nth-child(n+3) {
        margin-top: 14px;
    }
    .ltcs-billing-invoice .title-block {
        position: relative;
        text-align: center;
    }
    .ltcs-billing-invoice .title-block > .date {
        position: absolute;
        left: 30px;
    }
    .ltcs-billing-invoice .year,
    .ltcs-billing-invoice .month,
    .ltcs-billing-invoice .day {
        display: inline-block;
        min-width: 28px;
    }
    .ltcs-billing-invoice .title {
        font-size: 1.3rem;
        text-align: center;
        height: 40px;
        line-height: 40px;
    }
    .ltcs-billing-invoice .date-table {
        height: 40px;
    }
    .ltcs-billing-invoice .user-block {
        padding: 24px 42px 24px 16px;
        flex: 1;
    }
    .ltcs-billing-invoice .office-block {
        flex: 0.9;
    }
    .ltcs-billing-invoice .office-table {
        width: 100%;
    }
    .ltcs-billing-invoice .office-table > tbody > tr:first-of-type > td:first-child {
        width: 20%;
    }
    .ltcs-billing-invoice .office-table > tbody > tr:first-of-type > td:not(:first-child) {
        width: 8%;
    }
    .ltcs-billing-invoice .office-table > tbody > tr:first-of-type,
    .ltcs-billing-invoice .office-table > tbody > tr:nth-of-type(2) > td:nth-of-type(2),
    .ltcs-billing-invoice .office-table > tbody > tr:nth-of-type(n+2):not(:nth-of-type(4)) > td:nth-of-type(1) {
        text-align: center;
    }
    .ltcs-billing-invoice .office-table > tbody > tr:nth-of-type(n+2):not(:nth-of-type(3)) > td:last-of-type {
        padding-left: 6px;
    }
    .ltcs-billing-invoice .table-title {
        display: inline-block;
        margin-left: 12px;
        margin-bottom: 4px;
    }
    .ltcs-billing-invoice .insurance-table,
    .ltcs-billing-invoice .public-expense-table {
        flex: 1;
        width: 100%;
    }
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2) > td {
        width: 8.4%;
    }
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2) > td:first-of-type,
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2) > td:nth-of-type(7) {
        width: 5%;
    }
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2) > td:nth-of-type(2) {
        width: 6.8%;
    }
    .ltcs-billing-invoice .public-expense-table > thead > tr:nth-of-type(2) > td {
        width: 11.65%;
    }
    .ltcs-billing-invoice .public-expense-table > tbody > tr:first-of-type > td:first-of-type {
        width: 2.5%;
    }
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2),
    .ltcs-billing-invoice .public-expense-table > thead > tr:nth-of-type(2) {
        border-bottom: solid 1px #000;
    }
    .ltcs-billing-invoice .insurance-table > thead,
    .ltcs-billing-invoice .public-expense-table > thead,
    .ltcs-billing-invoice .insurance-table > tbody > tr:last-of-type > td:first-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:first-of-type > td:first-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(n+3) > td:first-of-type {
        text-align: center;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:not(:last-of-type) > td:first-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:first-of-type > td:nth-of-type(2),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(2) > td:first-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(n+3):not(:last-of-type) > td:nth-of-type(2) {
        padding-left: 8px;
    }
    .ltcs-billing-invoice .insurance-table > tbody td:nth-of-type(n+2),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(2) > td:nth-of-type(n+2),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:not(:nth-of-type(2)) > td:nth-of-type(n+3) {
        padding-right: 6px;
        text-align: right;
    }
    .ltcs-billing-invoice .insurance-table thead > tr:first-of-type td:not(:last-of-type),
    .ltcs-billing-invoice .insurance-table thead > tr:not(:first-of-type) td:nth-of-type(6),
    .ltcs-billing-invoice .insurance-table tbody td:first-of-type,
    .ltcs-billing-invoice .insurance-table tbody td:nth-of-type(7),
    .ltcs-billing-invoice .public-expense-table thead > tr:first-of-type td:not(:last-of-type),
    .ltcs-billing-invoice .public-expense-table tbody > tr:not(:nth-of-type(2)):not(:last-of-type) td:nth-of-type(2),
    .ltcs-billing-invoice .public-expense-table tbody > tr:nth-of-type(2) td:first-of-type,
    .ltcs-billing-invoice .public-expense-table tbody > tr:last-of-type td:first-of-type {
        border-right: solid 2px #000;
    }
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2) td:nth-of-type(4),
    .ltcs-billing-invoice .insurance-table > thead > tr:nth-of-type(2) td:nth-last-of-type(3),
    .ltcs-billing-invoice .public-expense-table > thead > tr:nth-of-type(2) td:nth-of-type(4) {
        border-left: solid 2px #000;
        border-right: solid 2px #000;
        border-top: solid 2px #000;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:not(:last-of-type) td:nth-of-type(5),
    .ltcs-billing-invoice .insurance-table > tbody td:nth-last-of-type(3),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:not(:nth-of-type(2)):not(:last-of-type) td:nth-of-type(6),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(2) td:nth-of-type(5) {
        border-left: solid 2px #000;
        border-right: solid 2px #000;
    }
    .ltcs-billing-invoice .public-expense-table > thead > tr:nth-of-type(2) > td:last-of-type {
        border-left: solid 2px #000;
        border-top: solid 2px #000;
    }
    .ltcs-billing-invoice .public-expense-table > tbody td:last-of-type {
        border-left: solid 2px #000;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:last-of-type > td:not(:nth-of-type(5)):not(:last-of-type),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:last-of-type > td:not(:nth-of-type(5)):not(:last-of-type) {
        border-top: solid 1px #000;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:last-of-type td:nth-of-type(5),
    .ltcs-billing-invoice .insurance-table > tbody > tr:last-of-type td:last-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:last-of-type td:nth-of-type(5),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:last-of-type td:last-of-type {
        border: double 3px #000;
    }
    .ltcs-billing-invoice .public-expense-table > tbody > tr {
        height: 28px;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:nth-of-type(2),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(8),
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(12) {
        height: 52px;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:first-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:nth-of-type(2) {
        height: 76px;
    }
    .ltcs-billing-invoice .public-expense-table > tbody > tr:first-of-type {
        height: 100px;
    }
    .ltcs-billing-invoice .insurance-table > tbody > tr:last-of-type,
    .ltcs-billing-invoice .public-expense-table > tbody > tr:last-of-type {
        height: 42px;
    }
    </style>
@endpush

<div class="ltcs-billing-invoice format-block"><span style="font-weight: bold;">様式第一</span>(附則第二条関係)</div>
<div class="ltcs-billing-invoice outer flex column">
    <div class="title-block">
        <div class="date">
            <table class="thick-border date-table">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 12px;">{{ $invoice->providedIn['japaneseCalender'] }}</td>
                        <td>{{ mb_substr($invoice->providedIn['year'], 0, 1) }}</td>
                        <td>{{ mb_substr($invoice->providedIn['year'], 1, 1) }}</td>
                        <td style="padding: 0 6px;">年</td>
                        <td>{{ mb_substr($invoice->providedIn['month'], 0, 1) }}</td>
                        <td>{{ mb_substr($invoice->providedIn['month'], 1, 1) }}</td>
                        <td style="padding: 0 6px;">月分</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <h1 class="title">介護給付費請求書</h1>
    </div>
    <div class="flex" style="gap: 36px; margin-bottom: 4px;">
        <div class="user-block flex column justify-around">
            <div style="margin-left: 8px;">保 険 者</div>
            <div style="margin-left: 24px;">(別 記)殿</div>
            <div class="flex justify-between">
                下記のとおり請求します。
                <div class="text-center">
                    <span class="date">
                        {{ \Domain\Common\Carbon::today()->formatLocalized('%EC') }}
                        <span class="year">{{ \Domain\Common\Carbon::today()->formatLocalized('%Ey') }}</span>年
                        <span class="month">{{ intval(\Domain\Common\Carbon::today()->formatLocalized('%m')) }}</span>月
                        <span class="day">{{ intval(\Domain\Common\Carbon::today()->formatLocalized('%d')) }}</span>日
                    </span>
                </div>
            </div>
        </div>
        <div class="office-block">
            <div class="flex">
                <table class="thick-border office-table">
                    <tbody>
                        <tr class="font-small office-number" style="height: 20px">
                            <td>事業所番号</td>
                            @for($i = 0; $i < 10; $i++)
                                <td>{{ mb_substr($invoice->office->code, $i, 1) === '' ? ' ' : mb_substr($invoice->office->code, $i, 1) }}</td>
                            @endfor
                        </tr>
                        <tr style="height: 48px">
                            <td rowspan="4">請求事業所</td>
                            <td colspan="2">名 称</td>
                            <td colspan="8" class="font-small">{{ $invoice->office->name }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" rowspan="2">所在地</td>
                            <td colspan="8" style="padding: 0">
                                <table>
                                    <tbody>
                                        <tr class="text-center">
                                            <td>〒</td>
                                            @for($i = 0; $i < 8; $i++)
                                                <td>{{ mb_substr($invoice->office->addr->postcode, $i, 1) === '' ? ' ' : mb_substr($invoice->office->addr->postcode, $i, 1) }}</td>
                                            @endfor
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr style="height: 60px">
                            <td colspan="8" class="font-small" style="vertical-align: baseline;">
                                {{ \Domain\Common\Prefecture::resolve($invoice->office->addr->prefecture) . $invoice->office->addr->city . $invoice->office->addr->street . ' ' . $invoice->office->addr->apartment}}
                            </td>
                        </tr>
                        <tr style="height: 30px">
                            <td colspan="2">連絡先</td>
                            <td colspan="8">{{ $invoice->office->tel }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="margin-bottom: 10px;">
        <span class="table-title">保険請求</span>
        <table class="thick-border insurance-table">
            <thead>
                <tr>
                    <td rowspan="2">区分</td>
                    <td colspan="6">サービス費用</td>
                    <td colspan="5">特定入所者介護サービス費等</td>
                </tr>
                <tr>
                    <td>件数</td>
                    <td>単位数<br>・点数</td>
                    <td>費用<br>合計</td>
                    <td>保険<br>請求額</td>
                    <td>公費<br>請求額</td>
                    <td>利用者<br>負担</td>
                    <td>件数</td>
                    <td>費用<br>合計</td>
                    <td>利用者<br>負担</td>
                    <td>公費<br>請求額</td>
                    <td>保険<br>請求額</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>居宅・施設サービス<br>介護予防サービス<br>地域密着型サービス等</td>
                    <td>{{ $invoice->insurance->statementCount }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->totalScore) >= 9])>{{ $invoice->insurance->totalScore }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->totalFee) >= 10])>{{ $invoice->insurance->totalFee }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->insuranceAmount) >= 10])>{{ $invoice->insurance->insuranceAmount }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->subsidyAmount) >= 10])>{{ $invoice->insurance->subsidyAmount }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->copayAmount) >= 10])>{{ $invoice->insurance->copayAmount }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>居宅介護支援・<br>介護予防支援</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>合計</td>
                    <td>{{ $invoice->insurance->statementCount }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->totalScore) >= 9])>{{ $invoice->insurance->totalScore }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->totalFee) >= 10])>{{ $invoice->insurance->totalFee }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->insuranceAmount) >= 10])>{{ $invoice->insurance->insuranceAmount }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->subsidyAmount) >= 10])>{{ $invoice->insurance->subsidyAmount }}</td>
                    <td @class(['font-small' => mb_strlen($invoice->insurance->copayAmount) >= 10])>{{ $invoice->insurance->copayAmount }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <span class="table-title">公費請求</span>
        <table class="thick-border public-expense-table">
            <thead>
                <tr>
                    <td colspan="2" rowspan="2">区分</td>
                    <td colspan="4">サービス費用</td>
                    <td colspan="3">特定入所者介護サービス費等</td>
                </tr>
                <tr>
                    <td>件数</td>
                    <td>単位数<br>・点数</td>
                    <td>費用<br>合計</td>
                    <td>公費<br>請求額</td>
                    <td>件数</td>
                    <td>費用<br>合計</td>
                    <td>公費<br>請求額</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="2">12</td>
                    <td>生 保<br>居宅・施設サービス<br>介護予防サービス<br>地域密着型サービス等</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::livelihoodProtection()->value()]->statementCount }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::livelihoodProtection()->value()]->totalScore }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::livelihoodProtection()->value()]->totalFee }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::livelihoodProtection()->value()]->subsidyAmount }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>生 保<br>居宅介護支援・<br>介護予防支援</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>10</td>
                    <td>感染症 37 条の 2</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>21</td>
                    <td>障自・通院医療</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>15</td>
                    <td>障自・更生医療</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>19</td>
                    <td>原爆・一般</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>54</td>
                    <td>難病法</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>51</td>
                    <td>特定疾患等<br>治療研究</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>81</td>
                    <td>被爆者助成</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::atomicBombVictim()->value()]->statementCount }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::atomicBombVictim()->value()]->totalScore }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::atomicBombVictim()->value()]->totalFee }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::atomicBombVictim()->value()]->subsidyAmount }}</td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>86</td>
                    <td>被爆体験者</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>87</td>
                    <td>有機ヒ素・緊急措置</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>88</td>
                    <td>水俣病総合対策<br>メチル水銀</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>66</td>
                    <td>石綿・救済措置</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>58</td>
                    <td>障害者・支援措置(全額免除)</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::pwdSupport()->value()]->statementCount }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::pwdSupport()->value()]->totalScore }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::pwdSupport()->value()]->totalFee }}</td>
                    <td>{{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::pwdSupport()->value()]->subsidyAmount }}</td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td>25</td>
                    <td>中国残留邦人等</td>
                    <td>
                        {{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::supportForJapaneseReturneesFromChina()->value()]->statementCount }}
                    </td>
                    <td>
                        {{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::supportForJapaneseReturneesFromChina()->value()]->totalScore }}
                    </td>
                    <td>
                        {{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::supportForJapaneseReturneesFromChina()->value()]->totalFee }}
                    </td>
                    <td>
                        {{ $invoice->subsidy->items[\Domain\Common\DefrayerCategory::supportForJapaneseReturneesFromChina()->value()]->subsidyAmount }}
                    </td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                </tr>
                <tr>
                    <td colspan="2">合計</td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td>{{ $invoice->subsidy->subsidyAmountTotal }}</td>
                    <td class="no-use"></td>
                    <td class="no-use"></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
