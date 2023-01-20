@push('css')
    <style>
    .ltcs-statements.format-block {
        margin-left: 10px;
    }
    .ltcs-statements.outer {
        height: 100%;
        padding: 0 12px 14px 18px;
        width: 100%;
    }
    .ltcs-statements.outer > *:nth-child(2) {
        margin: 2px 0;
    }
    .ltcs-statements.outer > *:nth-child(n+4) {
        margin-top: 2px;
    }
    .ltcs-statements .title {
        font-size: 1rem;
        text-align: center;
    }
    .ltcs-statements .subtitle {
        font-size: 0.7rem;
        font-weight: normal;
    }
    .ltcs-statements .thin-width {
        width: 22px;
    }
    .ltcs-statements tr:not(:last-of-type) {
        border-bottom: solid 1px #000;
    }
    .ltcs-statements tr {
        height: 24px;
        page-break-inside: avoid !important;
    }
    .ltcs-statements .birth-date-table,
    .ltcs-statements .plan-table,
    .ltcs-statements .date-table,
    .ltcs-statements .details-table,
    .ltcs-statements .details-special-case-table,
    .ltcs-statements .totalling-table,
    .ltcs-statements .alleviation-table {
        font-size: 0.8rem;
    }
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:last-of-type,
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:last-of-type,
    .ltcs-statements .totalling-table > tbody > tr > td:first-of-type {
        font-size: 0.7rem;
    }
    .ltcs-statements .insured-table > tbody > tr:first-of-type > td:nth-of-type(2),
    .ltcs-statements .insured-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .office-table > tbody > tr:first-of-type > td:nth-of-type(2),
    .ltcs-statements .office-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .office-table > tbody > tr:last-of-type > td:last-of-type {
        padding: 0 4px;
        text-align: left;
    }
    .ltcs-statements .birth-date-table > tbody > tr {
        border-bottom: none;
        height: 22px;
    }
    .ltcs-statements .birth-date-table {
        border: none;
    }
    .ltcs-statements .birth-date-table > tbody > tr:first-of-type > td:first-of-type {
        border-bottom: solid 1px #000;
    }
    .ltcs-statements .birth-date-table > tbody > tr:first-of-type > td:nth-last-of-type(2),
    .ltcs-statements .birth-date-table > tbody > tr:last-of-type td {
        border-right: solid 1px #000;
        min-width: 0;
        width: 7.2%;
    }
    .ltcs-statements .plan-table > tbody > tr:first-of-type > td,
    .ltcs-statements .plan-table > tbody > tr:last-of-type > td:first-of-type,
    .ltcs-statements .plan-table > tbody > tr:last-of-type > td:nth-last-of-type(-n+2),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:last-of-type,
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:last-of-type,
    .ltcs-statements .totalling-table > tbody > tr > td:first-of-type,
    .ltcs-statements .alleviation-table > tbody > tr:first-of-type > td:first-of-type,
    .ltcs-statements .alleviation-table > tbody > tr:not(:first-of-type) > td:nth-of-type(2),
    .ltcs-statements .alleviation-table > tbody > tr:not(:first-of-type) > td:last-of-type {
        padding-left: 6px;
        text-align: left;
    }
    .ltcs-statements .plan-table > tbody > tr:first-of-type > td:first-of-type,
    .ltcs-statements .plan-table > tbody > tr:last-of-type > td:first-of-type,
    .ltcs-statements .plan-table > tbody > tr:last-of-type > td:nth-last-of-type(2),
    .ltcs-statements .date-table > tbody > tr > td:first-of-type,
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-of-type(2n+2):not(:nth-of-type(6)):not(:nth-of-type(14)):not(:nth-of-type(20)),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-of-type(5),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-of-type(7),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-of-type(11),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-of-type(13),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-last-of-type(2),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-last-of-type(4),
    .ltcs-statements .date-table > tbody > tr:first-of-type > td:nth-last-of-type(8),
    .ltcs-statements .details-table > tbody > tr:first-of-type > td:not(:last-of-type),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:nth-of-type(7),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:nth-of-type(11),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:nth-of-type(13),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:nth-of-type(19),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:nth-of-type(21),
    .ltcs-statements .details-table > tbody > tr:not(:first-of-type) > td:nth-of-type(27),
    .ltcs-statements .details-special-case-table > tbody > tr:first-of-type > td:not(:last-of-type),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:first-of-type,
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(7),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(11),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(13),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(19),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(21),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(27),
    .ltcs-statements .details-special-case-table > tbody > tr:not(:first-of-type) > td:nth-of-type(28),
    .ltcs-statements .totalling-table > tbody > tr:first-of-type > td:first-of-type,
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(-n+2) > td:first-of-type,
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(-n+2) > td:nth-of-type(3n+3),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(-n+2) > td:nth-of-type(3n+4),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(n+3):not(:nth-of-type(8)) > td:nth-of-type(6n+1),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(n+3):not(:nth-of-type(8)) > td:nth-of-type(6n+3):not(:nth-of-type(n+26)),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(8) > td:nth-of-type(5n+1),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(8) > td:nth-of-type(5n+3),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(6) > td:nth-last-of-type(4),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(7) > td:nth-last-of-type(4),
    .ltcs-statements .alleviation-table > tbody > tr:first-of-type > td:not(:nth-of-type(3)):not(:nth-of-type(4)):not(:nth-of-type(5)),
    .ltcs-statements .alleviation-table > tbody > tr:not(:first-of-type) > td:nth-of-type(1),
    .ltcs-statements .alleviation-table > tbody > tr:not(:first-of-type) > td:nth-of-type(6n+2) {
        border-right: solid 2px #000;
    }
    .ltcs-statements .plan-table > tbody > tr:first-of-type > td:last-of-type,
    .ltcs-statements .date-table > tbody > tr:first-of-type,
    .ltcs-statements .details-table > tbody > tr:first-of-type,
    .ltcs-statements .totalling-table > tbody > tr:first-of-type > td:last-of-type,
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(2) > td:nth-of-type(3n+4),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(5) > td:last-of-type,
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(6) > td:nth-last-of-type(4),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(7) > td:nth-last-of-type(-n+4),
    .ltcs-statements .totalling-table > tbody > tr:nth-of-type(8) > td:last-of-type,
    .ltcs-statements .alleviation-table > tbody > tr:first-of-type {
        border-bottom: solid 2px #000;
    }
    .ltcs-statements .page-table td {
        min-width: 44px;
    }
    .ltcs-statements .era > span:not(:first-of-type) {
        margin-left: 6px;
    }
    .ltcs-statements .care-level > span:not(:first-of-type) {
        padding: 0 4px;
    }
    .ltcs-statements .maker > span:not(:first-of-type) {
        margin-left: 60px;
    }
    .ltcs-statements .reason > span:not(:first-of-type) {
        margin-left: 8px;
    }
    .ltcs-statements .selected {
        position: relative;
    }
    .ltcs-statements .selected::after {
        border-radius: 50%;
        border: solid 1px #000;
        bottom: 0;
        content: '';
        height: 100%;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: 100%;
    }
    .ltcs-statements .triangle {
        position: relative;
    }
    .ltcs-statements .triangle::after {
        bottom: -4px;
        content: '\25B2';
        font-size: 0.7rem;
        position: absolute;
        right: -6px;
    }
    </style>
@endpush

<div class="ltcs-statements format-block"><span style="font-weight: bold;">様式第二</span>(附則第二条関係)</div>
<div class="ltcs-statements outer flex column">
    <h1 class="title">居宅サービス・地域密着型サービス介護給付費明細書</h1>
    <div class="subtitle text-center">
        <div>(訪問介護・訪問入浴介護・訪問看護・訪問リハ・居宅療養管理指導・通所介護・通所リハ・福祉用具貸与・定期巡回・随時対応型訪問介護看護・</div>
        <div>夜間対応型訪問介護・地域密着型通所介護・認知症対応型通所介護・小規模多機能型居宅介護(短期利用以外)・小規模多機能型居宅介護(短期利用)・</div>
        <div>複合型サービス(看護小規模多機能型居宅介護・短期利用以外)・複合型サービス(看護小規模多機能型居宅介護・短期利用))</div>
    </div>
    <div class="flex justify-between">
        <div>
            <table class="thick-border">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 8px;">公費負担者番号</td>
                        @for($i = 0; $i < 8; $i++)
                            <td>{{ mb_substr($pdf->defrayerNumber, $i, 1) === '' ? ' ' : mb_substr($pdf->defrayerNumber, $i, 1) }}</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
            <table class="thick-border-right thick-border-bottom thick-border-left">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 8px;">公費受給者番号</td>
                        @for($i = 0; $i < 7; $i++)
                            <td>{{ mb_substr($pdf->recipientNumber, $i, 1) === '' ? ' ' : mb_substr($pdf->recipientNumber, $i, 1) }}</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex column">
            <table class="thick-border">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 12px;">{{ $pdf->providedIn['japaneseCalender'] }}</td>
                        <td>{{ mb_substr($pdf->providedIn['year'], 0, 1) }}</td>
                        <td>{{ mb_substr($pdf->providedIn['year'], 1, 1) }}</td>
                        <td style="padding: 0 6px;">年</td>
                        @for($i = 0; $i < 2; $i++)
                            <td>{{ mb_substr($pdf->providedIn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->providedIn['month'], $i, 1) }}</td>
                        @endfor
                        <td style="padding: 0 6px;">月分</td>
                    </tr>
                </tbody>
            </table>
            <table class="thick-border" style="margin-top: 4px;">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 4px;">保険者番号</td>
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($pdf->insurerNumber, $i, 1) === '' ? ' ' : mb_substr($pdf->insurerNumber, $i, 1) }}</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="flex justify-between">
        <table class="thick-border insured-table">
            <tbody class="text-center">
                <tr>
                    <td rowspan="6" class="thin-width">被保険者</td>
                    <td class="font-x-small">被保険者<br>番号</td>
                    @for($i = 0; $i < 10; $i++)
                        <td>{{ mb_substr($pdf->user->insNumber, $i, 1) === '' ? ' ' : mb_substr($pdf->user->insNumber, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr style="border-bottom: none;">
                    <td class="font-x-small">(フリガナ)</td>
                    <td colspan="10" class="font-small thin-border-bottom">{{ $pdf->user->name->phoneticDisplayName }}</td>
                </tr>
                <tr>
                    <td class="x-small">氏名</td>
                    <td colspan="10" class="font-small" style="height: 36px;">{{ $pdf->user->name->displayName }}</td>
                </tr>
                <tr>
                    <td>生年月日</td>
                    <td colspan="10">
                        <table class="full-width birth-date-table">
                            <tbody>
                                <tr>
                                    <td colspan="9">
                                        <div class="flex justify-center era">
                                            <span @class(['selected' => $pdf->userBirthday['japaneseCalender'] === '明治'])>1.明治</span>
                                            <span @class(['selected' => $pdf->userBirthday['japaneseCalender'] === '大正'])>2.大正</span>
                                            <span @class(['selected' => $pdf->userBirthday['japaneseCalender'] === '昭和'])>3.昭和</span>
                                        </div>
                                    </td>
                                    <td rowspan="2">性<br>別</td>
                                    <td rowspan="2">
                                        <div class="flex justify-around">
                                            <span @class(['selected' => $pdf->user->sex->value() === \Domain\Common\Sex::male()->value()])>1.男</span>
                                            <span @class(['selected' => $pdf->user->sex->value() === \Domain\Common\Sex::female()->value()])>2.女</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ mb_substr($pdf->userBirthday['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($pdf->userBirthday['year'], 1, 1) }}</td>
                                    <td>年</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userBirthday['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->userBirthday['month'], $i, 1) }}</td>
                                    @endfor
                                    <td>月</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userBirthday['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->userBirthday['day'], $i, 1) }}</td>
                                    @endfor
                                    <td>日</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="font-x-small">要介護<br>状態区分</td>
                    <td colspan="10" class="font-small">
                        <div class="flex justify-center care-level">
                            <span style="margin-right: 2px;">要介護</span>
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel1()->value()])>1</span>・
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel2()->value()])>2</span>・
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel3()->value()])>3</span>・
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel4()->value()])>4</span>・
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel5()->value()])>5</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="font-small">認定有効<br>期間</td>
                    <td colspan="10">
                        <table>
                            <tbody>
                                <tr>
                                    <td class="font-x-small">
                                        <div @class(['selected' => $pdf->userActivatedOn['japaneseCalender'] === '平成'])>1.平成</div>
                                        <div @class(['selected' => $pdf->userActivatedOn['japaneseCalender'] === '令和'])>2.令和</div>
                                    </td>
                                    <td>{{ mb_substr($pdf->userActivatedOn['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($pdf->userActivatedOn['year'], 1, 1) }}</td>
                                    <td>年</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userActivatedOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->userActivatedOn['month'], $i, 1) }}</td>
                                    @endfor
                                    <td>月</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userActivatedOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->userActivatedOn['day'], $i, 1) }}</td>
                                    @endfor
                                    <td>日</td>
                                    <td>から</td>
                                </tr>
                                <tr>
                                    <td>令和</td>
                                    <td>{{ mb_substr($pdf->userDeactivatedOn['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($pdf->userDeactivatedOn['year'], 1, 1) }}</td>
                                    <td>年</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userDeactivatedOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->userDeactivatedOn['month'], $i, 1) }}</td>
                                    @endfor
                                    <td>月</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userDeactivatedOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->userDeactivatedOn['day'], $i, 1) }}</td>
                                    @endfor
                                    <td>日</td>
                                    <td>まで</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="thick-border office-table">
            <tbody class="text-center">
                <tr>
                    <td rowspan="5" class="thin-width">請求事業者</td>
                    <td class="font-x-small">事業所<br>番号</td>
                    @for($i = 0; $i < 10; $i++)
                        <td>{{ mb_substr($pdf->office->code, $i, 1) === '' ? ' ' : mb_substr($pdf->office->code, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td class="font-x-small">事業所<br>名称</td>
                    <td colspan="10" class="font-small">{{ $pdf->office->name }}</td>
                </tr>
                <tr>
                    <td rowspan="2">所在地</td>
                    <td colspan="10">
                        <table>
                            <tbody>
                                <tr>
                                    <td>〒</td>
                                    @for($i = 0; $i < 8; $i++)
                                        <td>{{ mb_substr($pdf->office->addr->postcode, $i, 1) === '' ? ' ' : mb_substr($pdf->office->addr->postcode, $i, 1) }}</td>
                                    @endfor
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr style="height: 80px">
                    <td colspan="10" class="font-small" style="vertical-align: baseline;">{{ \Domain\Common\Prefecture::resolve($pdf->office->addr->prefecture) . $pdf->office->addr->city . $pdf->office->addr->street . ' ' . $pdf->office->addr->apartment}}</td>
                </tr>
                <tr style="height: 36px">
                    <td>連絡先</td>
                    <td colspan="10">
                        電話番号
                        <span class="font-nomal">{{ $pdf->office->tel }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="thick-border full-width plan-table">
        <tbody>
            <tr>
                <td rowspan="2" class="font-x-small">居宅<br>サービス<br>計画</td>
                <td colspan="13">
                    <div class="flex maker">
                        <span @class(['selected' => $pdf->carePlanAuthor->authorType->value() === \Domain\LtcsInsCard\LtcsCarePlanAuthorType::careManagerOffice()->value()])>1.居宅介護支援事業者作成</span>
                        <span @class(['selected' => $pdf->carePlanAuthor->authorType->value() === \Domain\LtcsInsCard\LtcsCarePlanAuthorType::self()->value()])>2.被保険者自己作成</span>
                    </div>
                </td>
            </tr>
            <tr class="font-nomal text-center">
                <td class="font-x-small">事業所<br>番号</td>
                @for($i = 0; $i < 10; $i++)
                    <td>{{ mb_substr($pdf->carePlanAuthor->code, $i, 1) === '' ? ' ' : mb_substr($pdf->carePlanAuthor->code, $i, 1) }}</td>
                @endfor
                <td class="font-x-small">事業所<br>名称</td>
                <td style="width: 40%;">{{ $pdf->carePlanAuthor->name }}</td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border full-width date-table">
        <tbody>
            <tr class="text-center">
                <td class="font-x-small">開始<br>年月日</td>
                <td class="font-x-small">
                    <div @class(['selected' => $pdf->agreedOn['japaneseCalender'] === '平成'])>1.平成</div>
                    <div @class(['selected' => $pdf->agreedOn['japaneseCalender'] === '令和'])>2.令和</div>
                </td>
                <td>{{ mb_substr($pdf->agreedOn['year'], 0, 1) }}</td>
                <td>{{ mb_substr($pdf->agreedOn['year'], 1, 1) }}</td>
                <td>年</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->agreedOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->agreedOn['month'], $i, 1) }}</td>
                @endfor
                <td>月</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->agreedOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->agreedOn['day'], $i, 1) }}</td>
                @endfor
                <td>日</td>
                <td class="font-x-small">中止<br>年月日</td>
                <td>令和</td>
                <td>{{ mb_substr($pdf->expiredOn['year'], 0, 1) }}</td>
                <td>{{ mb_substr($pdf->expiredOn['year'], 1, 1) }}</td>
                <td>年</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->expiredOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->expiredOn['month'], $i, 1) }}</td>
                @endfor
                <td>月</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->expiredOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->expiredOn['day'], $i, 1) }}</td>
                @endfor
                <td>日</td>
            </tr>
            <tr>
                <td class="font-x-small text-center">中止<br>理由</td>
                <td colspan="21">
                    <div class="reason" style="margin-left: 4px;">
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::notApplicable()->value()])>1.非該当</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::hospitalized()->value()])>3.医療機関入院</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::died()->value()])>4.死亡</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::other()->value()])>5.その他</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToWelfareFacility()->value()])>6.介護老人福祉施設入所</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToHealthCareFacility()->value()])>7.介護老人保健施設入所</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToMedicalLongTermCareSanatoriums()->value()])>8.介護療養型医療施設入院</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToCareAidMedicalCenter()->value()])>9.介護医療院入所</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border full-width details-table">
        <tbody class="text-center">
            <tr class="text-center" style="height: 36px;">
                <td rowspan="11" class="thin-width">給付費明細欄</td>
                <td style="width: 20%;">サービス内容</td>
                <td colspan="6" style="width: 14.2%;">サービスコード</td>
                <td colspan="4" style="width: 9.46%;">単位数</td>
                <td colspan="2" style="width: 4.73%;">回数</td>
                <td colspan="6" style="width: 14.2%;">サービス単位数</td>
                <td colspan="2" class="font-x-small" style="width: 4.73%;">公費分<br>回数</td>
                <td colspan="6" style="width: 14.2%;">公費対象単位数</td>
                <td>摘要</td>
            </tr>
            @foreach($pdf->items as $item)
                <tr>
                    <td>{{ $item->serviceName }}</td>
                    {{-- サービスコード --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($item->serviceCode, $i, 1) === '' ? ' ' : mb_substr($item->serviceCode, $i, 1) }}</td>
                    @endfor
                    {{-- 単位数 --}}
                    @for($i = 0; $i < 4; $i++)
                        <td>{{ mb_substr($item->unitScore, $i, 1) === '' ? ' ' : mb_substr($item->unitScore, $i, 1) }}</td>
                    @endfor
                    {{-- 回数 --}}
                    @for($i = 0; $i < 2; $i++)
                        <td>{{ mb_substr($item->count, $i, 1) === '' ? ' ' : mb_substr($item->count, $i, 1) }}</td>
                    @endfor
                    {{-- サービス単位数 --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($item->totalScore, $i, 1) === '' ? ' ' : mb_substr($item->totalScore, $i, 1) }}</td>
                    @endfor
                    {{-- 公費分回数 --}}
                    @for($i = 0; $i < 2; $i++)
                        <td>{{ mb_substr($item->subsidyCount, $i, 1) === '' ? ' ' : mb_substr($item->subsidyCount, $i, 1) }}</td>
                    @endfor
                    {{-- 公費対象単位数 --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($item->subsidyScore, $i, 1) === '' ? ' ' : mb_substr($item->subsidyScore, $i, 1) }}</td>
                    @endfor
                    {{-- 摘要 --}}
                    <td>{{ $item->note }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < $pdf->extraItemRows(); $i++)
                <tr>
                    <td></td>
                    {{-- サービスコード --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 単位数 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 回数 --}}
                    <td></td>
                    <td></td>
                    {{-- サービス単位数 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 公費分回数 --}}
                    <td></td>
                    <td></td>
                    {{-- 公費対象単位数 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 摘要 --}}
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <table class="thick-border full-width details-special-case-table">
        <tbody class="text-center">
            <tr class="text-center" style="height: 36px;">
                <td rowspan="4" style="width: 43px;">
                    <div class="align-center flex font-x-small justify-around">
                        <div>給<br>付<br>費<br>明<br>細<br>欄</div>
                        <div>対<br>象<br>者<br>&#xFE36;</div>
                        <div>&#xFE35;<br>住<br>所<br>地<br>特<br>例</div>
                    </div>
                </td>
                <td style="width: 16%;">サービス内容</td>
                <td colspan="6" style="width: 14.2%;">サービスコード</td>
                <td colspan="4" style="width: 9.46%;">単位数</td>
                <td colspan="2" style="width: 4.73%;">回数</td>
                <td colspan="6" style="width: 14.2%;">サービス単位数</td>
                <td colspan="2" class="font-x-small" style="width: 4.73%;">公費分<br>回数</td>
                <td colspan="6" style="width: 14.2%;">公費対象単位数</td>
                <td class="font-x-small" style="width: 7%;">施設所在<br>保険者番号</td>
                <td>摘要</td>
            </tr>
            @for($i = 0; $i < 3; $i++)
                <tr>
                    <td></td>
                    {{-- サービスコード --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 単位数 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 回数 --}}
                    <td></td>
                    <td></td>
                    {{-- サービス単位数 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 公費分回数 --}}
                    <td></td>
                    <td></td>
                    {{-- 公費対象単位数 --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- 施設所在保険者番号 --}}
                    <td></td>
                    {{-- 摘要 --}}
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <div class="flex">
        <div class="thick-border-top thick-border-left thick-border-bottom thin-width font-small flex align-center text-center">請求額集計欄</div>
        <table class="thick-border text-center totalling-table" style="width: calc(100% - 22px);">
            <tbody>
                <tr>
                    <td style="width: 18%;">①サービス種類コード<br>/②名称</td>
                    @foreach($pdf->aggregates as $aggregate)
                        <td>{{ mb_substr($aggregate->serviceDivisionCode, 0, 1) }}</td>
                        <td>{{ mb_substr($aggregate->serviceDivisionCode, 1, 1) }}</td>
                        <td colspan="4">{{ $aggregate->resolvedServiceDivisionCode }}</td>
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td colspan="4"></td>
                    @endfor
                    <td colspan="6" rowspan="4"></td>
                </tr>
                <tr>
                    <td>③サービス実日数</td>
                    @foreach($pdf->aggregates as $aggregate)
                        <td>{{ mb_substr($aggregate->serviceDays, 0, 1) }}</td>
                        <td>{{ mb_substr($aggregate->serviceDays, 1, 1) }}</td>
                        <td colspan="4" class="text-left font-x-small">日</td>
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td colspan="4" class="text-left font-x-small">日</td>
                    @endfor
                </tr>
                <tr>
                    <td>④計画単位数</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->plannedScore, $i, 1) === '' ? ' ' : mb_substr($aggregate->plannedScore, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                </tr>
                <tr>
                    <td>⑤限度額管理対象単位数</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->managedScore, $i, 1) === '' ? ' ' : mb_substr($aggregate->managedScore, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                </tr>
                <tr>
                    <td>⑥限度額管理対象外単位数</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->unmanagedScore, $i, 1) === '' ? ' ' : mb_substr($aggregate->unmanagedScore, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    <td colspan="6" class="font-x-small">給付率(/100)</td>
                </tr>
                <tr>
                    <td>⑦給付単位数(④⑤のうち少ない数)+⑥</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->totalScore, $i, 1) === '' ? ' ' : mb_substr($aggregate->totalScore, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    <td colspan="3" class="font-x-small">保険</td>
                    @for($i = 0; $i < 3; $i++)
                        <td>{{ mb_substr($pdf->insuranceBenefitRate, $i, 1) === '' ? ' ' : mb_substr($pdf->insuranceBenefitRate, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>⑧公費分単位数</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->subsidyTotalScore, $i, 1) === '' ? ' ' : mb_substr($aggregate->subsidyTotalScore, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    <td colspan="3" class="font-x-small">公費</td>
                    @for($i = 0; $i < 3; $i++)
                        <td>{{ $pdf->subsidyBenefitRate[$i + 1] }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>⑨単位数単価</td>
                    @foreach($pdf->aggregates as $aggregate)
                        <td>{{ mb_substr($aggregate->insuranceUnitCost, 0, 1) }}</td>
                        <td class="triangle">{{ mb_substr($aggregate->insuranceUnitCost, 1, 1) }}</td>
                        <td>{{ mb_substr($aggregate->insuranceUnitCost, 2, 1) }}</td>
                        <td>{{ mb_substr($aggregate->insuranceUnitCost, 3, 1) }}</td>
                        <td colspan="2" class="font-x-small">円/単位</td>
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td class="triangle"></td>
                        <td></td>
                        <td></td>
                        <td colspan="2" class="font-x-small">円/単位</td>
                    @endfor
                    <td colspan="6" class="font-x-small">合計</td>
                </tr>
                <tr>
                    <td>⑩保険請求額</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->insuranceClaimAmount, $i, 1) === '' ? ' ' : mb_substr($aggregate->insuranceClaimAmount, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    {{-- 合計 --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalInsuranceClaimAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalInsuranceClaimAmount, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>&#x246A;利用者負担額</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->insuranceCopayAmount, $i, 1) === '' ? ' ' : mb_substr($aggregate->insuranceCopayAmount, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    {{-- 合計 --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalInsuranceCopayAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalInsuranceCopayAmount, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>&#x246B;公費請求額</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->subsidyClaimAmount, $i, 1) === '' ? ' ' : mb_substr($aggregate->subsidyClaimAmount, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    {{-- 合計 --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalSubsidyClaimAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalSubsidyClaimAmount, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>&#x246C;公費分本人負担</td>
                    @foreach($pdf->aggregates as $aggregate)
                        @for($i = 0; $i < 6; $i++)
                            <td>{{ mb_substr($aggregate->subsidyCopayAmount, $i, 1) === '' ? ' ' : mb_substr($aggregate->subsidyCopayAmount, $i, 1) }}</td>
                        @endfor
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endfor
                    {{-- 合計 --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalSubsidyCopayAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalSubsidyCopayAmount, $i, 1) }}</td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
    <table class="thick-border full-width alleviation-table">
        <tbody class="text-center">
            <tr class="text-center" style="height: 36px;">
                <td rowspan="4" style="width: 8%;">社会福祉法人等による軽減欄</td>
                <td colspan="2">軽減率</td>
                <td style="width: 24px;"></td>
                <td style="width: 24px;"></td>
                <td class="triangle" style="width: 24px;"></td>
                <td style="width: 24px;"></td>
                <td style="width: 6%;">%</td>
                <td colspan="6" class="font-x-small" style="width: 17%;">
                    <div class="flex justify-center text-left">受領すべき利用者<br>負担の総額(円)</div>
                </td>
                <td colspan="6" style="width: 17%;">軽減額(円)</td>
                <td colspan="6" class="font-x-small" style="width: 17%;">
                    <div class="flex justify-center text-left">軽減後利用者<br>負担額(円)</div>
                </td>
                <td style="width: 17%;">備考</td>
            </tr>
            @for($i = 0; $i < 3; $i++)
                <tr>
                    <td style="width: 30px;"></td>
                    <td colspan="6"></td>
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
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <div class="flex justify-end">
        <table class="thick-border page-table">
            <tbody>
                {{-- TODO ページ数は未実装 --}}
                <tr class="text-center">
                    <td></td>
                    <td>枚中</td>
                    <td></td>
                    <td>枚目</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
