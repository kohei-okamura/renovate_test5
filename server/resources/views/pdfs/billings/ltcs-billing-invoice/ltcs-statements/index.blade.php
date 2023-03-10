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

<div class="ltcs-statements format-block"><span style="font-weight: bold;">????????????</span>(?????????????????????)</div>
<div class="ltcs-statements outer flex column">
    <h1 class="title">????????????????????????????????????????????????????????????????????????</h1>
    <div class="subtitle text-center">
        <div>(???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</div>
        <div>??????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????(??????????????????)????????????????????????????????????(????????????)???</div>
        <div>?????????????????????(????????????????????????????????????????????????????????????)????????????????????????(??????????????????????????????????????????????????????))</div>
    </div>
    <div class="flex justify-between">
        <div>
            <table class="thick-border">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 8px;">?????????????????????</td>
                        @for($i = 0; $i < 8; $i++)
                            <td>{{ mb_substr($pdf->defrayerNumber, $i, 1) === '' ? ' ' : mb_substr($pdf->defrayerNumber, $i, 1) }}</td>
                        @endfor
                    </tr>
                </tbody>
            </table>
            <table class="thick-border-right thick-border-bottom thick-border-left">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 8px;">?????????????????????</td>
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
                        <td style="padding: 0 6px;">???</td>
                        @for($i = 0; $i < 2; $i++)
                            <td>{{ mb_substr($pdf->providedIn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->providedIn['month'], $i, 1) }}</td>
                        @endfor
                        <td style="padding: 0 6px;">??????</td>
                    </tr>
                </tbody>
            </table>
            <table class="thick-border" style="margin-top: 4px;">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 4px;">???????????????</td>
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
                    <td rowspan="6" class="thin-width">????????????</td>
                    <td class="font-x-small">????????????<br>??????</td>
                    @for($i = 0; $i < 10; $i++)
                        <td>{{ mb_substr($pdf->user->insNumber, $i, 1) === '' ? ' ' : mb_substr($pdf->user->insNumber, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr style="border-bottom: none;">
                    <td class="font-x-small">(????????????)</td>
                    <td colspan="10" class="font-small thin-border-bottom">{{ $pdf->user->name->phoneticDisplayName }}</td>
                </tr>
                <tr>
                    <td class="x-small">??????</td>
                    <td colspan="10" class="font-small" style="height: 36px;">{{ $pdf->user->name->displayName }}</td>
                </tr>
                <tr>
                    <td>????????????</td>
                    <td colspan="10">
                        <table class="full-width birth-date-table">
                            <tbody>
                                <tr>
                                    <td colspan="9">
                                        <div class="flex justify-center era">
                                            <span @class(['selected' => $pdf->userBirthday['japaneseCalender'] === '??????'])>1.??????</span>
                                            <span @class(['selected' => $pdf->userBirthday['japaneseCalender'] === '??????'])>2.??????</span>
                                            <span @class(['selected' => $pdf->userBirthday['japaneseCalender'] === '??????'])>3.??????</span>
                                        </div>
                                    </td>
                                    <td rowspan="2">???<br>???</td>
                                    <td rowspan="2">
                                        <div class="flex justify-around">
                                            <span @class(['selected' => $pdf->user->sex->value() === \Domain\Common\Sex::male()->value()])>1.???</span>
                                            <span @class(['selected' => $pdf->user->sex->value() === \Domain\Common\Sex::female()->value()])>2.???</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ mb_substr($pdf->userBirthday['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($pdf->userBirthday['year'], 1, 1) }}</td>
                                    <td>???</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userBirthday['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->userBirthday['month'], $i, 1) }}</td>
                                    @endfor
                                    <td>???</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userBirthday['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->userBirthday['day'], $i, 1) }}</td>
                                    @endfor
                                    <td>???</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="font-x-small">?????????<br>????????????</td>
                    <td colspan="10" class="font-small">
                        <div class="flex justify-center care-level">
                            <span style="margin-right: 2px;">?????????</span>
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel1()->value()])>1</span>???
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel2()->value()])>2</span>???
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel3()->value()])>3</span>???
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel4()->value()])>4</span>???
                            <span @class(['selected' => $pdf->user->ltcsLevel->value() === \Domain\LtcsInsCard\LtcsLevel::careLevel5()->value()])>5</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="font-small">????????????<br>??????</td>
                    <td colspan="10">
                        <table>
                            <tbody>
                                <tr>
                                    <td class="font-x-small">
                                        <div @class(['selected' => $pdf->userActivatedOn['japaneseCalender'] === '??????'])>1.??????</div>
                                        <div @class(['selected' => $pdf->userActivatedOn['japaneseCalender'] === '??????'])>2.??????</div>
                                    </td>
                                    <td>{{ mb_substr($pdf->userActivatedOn['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($pdf->userActivatedOn['year'], 1, 1) }}</td>
                                    <td>???</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userActivatedOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->userActivatedOn['month'], $i, 1) }}</td>
                                    @endfor
                                    <td>???</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userActivatedOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->userActivatedOn['day'], $i, 1) }}</td>
                                    @endfor
                                    <td>???</td>
                                    <td>??????</td>
                                </tr>
                                <tr>
                                    <td>??????</td>
                                    <td>{{ mb_substr($pdf->userDeactivatedOn['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($pdf->userDeactivatedOn['year'], 1, 1) }}</td>
                                    <td>???</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userDeactivatedOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->userDeactivatedOn['month'], $i, 1) }}</td>
                                    @endfor
                                    <td>???</td>
                                    @for($i = 0; $i < 2; $i++)
                                        <td>{{ mb_substr($pdf->userDeactivatedOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->userDeactivatedOn['day'], $i, 1) }}</td>
                                    @endfor
                                    <td>???</td>
                                    <td>??????</td>
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
                    <td rowspan="5" class="thin-width">???????????????</td>
                    <td class="font-x-small">?????????<br>??????</td>
                    @for($i = 0; $i < 10; $i++)
                        <td>{{ mb_substr($pdf->office->code, $i, 1) === '' ? ' ' : mb_substr($pdf->office->code, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td class="font-x-small">?????????<br>??????</td>
                    <td colspan="10" class="font-small">{{ $pdf->office->name }}</td>
                </tr>
                <tr>
                    <td rowspan="2">?????????</td>
                    <td colspan="10">
                        <table>
                            <tbody>
                                <tr>
                                    <td>???</td>
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
                    <td>?????????</td>
                    <td colspan="10">
                        ????????????
                        <span class="font-nomal">{{ $pdf->office->tel }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="thick-border full-width plan-table">
        <tbody>
            <tr>
                <td rowspan="2" class="font-x-small">??????<br>????????????<br>??????</td>
                <td colspan="13">
                    <div class="flex maker">
                        <span @class(['selected' => $pdf->carePlanAuthor->authorType->value() === \Domain\LtcsInsCard\LtcsCarePlanAuthorType::careManagerOffice()->value()])>1.?????????????????????????????????</span>
                        <span @class(['selected' => $pdf->carePlanAuthor->authorType->value() === \Domain\LtcsInsCard\LtcsCarePlanAuthorType::self()->value()])>2.????????????????????????</span>
                    </div>
                </td>
            </tr>
            <tr class="font-nomal text-center">
                <td class="font-x-small">?????????<br>??????</td>
                @for($i = 0; $i < 10; $i++)
                    <td>{{ mb_substr($pdf->carePlanAuthor->code, $i, 1) === '' ? ' ' : mb_substr($pdf->carePlanAuthor->code, $i, 1) }}</td>
                @endfor
                <td class="font-x-small">?????????<br>??????</td>
                <td style="width: 40%;">{{ $pdf->carePlanAuthor->name }}</td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border full-width date-table">
        <tbody>
            <tr class="text-center">
                <td class="font-x-small">??????<br>?????????</td>
                <td class="font-x-small">
                    <div @class(['selected' => $pdf->agreedOn['japaneseCalender'] === '??????'])>1.??????</div>
                    <div @class(['selected' => $pdf->agreedOn['japaneseCalender'] === '??????'])>2.??????</div>
                </td>
                <td>{{ mb_substr($pdf->agreedOn['year'], 0, 1) }}</td>
                <td>{{ mb_substr($pdf->agreedOn['year'], 1, 1) }}</td>
                <td>???</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->agreedOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->agreedOn['month'], $i, 1) }}</td>
                @endfor
                <td>???</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->agreedOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->agreedOn['day'], $i, 1) }}</td>
                @endfor
                <td>???</td>
                <td class="font-x-small">??????<br>?????????</td>
                <td>??????</td>
                <td>{{ mb_substr($pdf->expiredOn['year'], 0, 1) }}</td>
                <td>{{ mb_substr($pdf->expiredOn['year'], 1, 1) }}</td>
                <td>???</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->expiredOn['month'], $i, 1) === '' ? ' ' : mb_substr($pdf->expiredOn['month'], $i, 1) }}</td>
                @endfor
                <td>???</td>
                @for($i = 0; $i < 2; $i++)
                    <td>{{ mb_substr($pdf->expiredOn['day'], $i, 1) === '' ? ' ' : mb_substr($pdf->expiredOn['day'], $i, 1) }}</td>
                @endfor
                <td>???</td>
            </tr>
            <tr>
                <td class="font-x-small text-center">??????<br>??????</td>
                <td colspan="21">
                    <div class="reason" style="margin-left: 4px;">
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::notApplicable()->value()])>1.?????????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::hospitalized()->value()])>3.??????????????????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::died()->value()])>4.??????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::other()->value()])>5.?????????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToWelfareFacility()->value()])>6.??????????????????????????????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToHealthCareFacility()->value()])>7.??????????????????????????????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToMedicalLongTermCareSanatoriums()->value()])>8.?????????????????????????????????</span>
                        <span @class(['selected' => $pdf->expiredReason === \Domain\Billing\LtcsExpiredReason::admittedToCareAidMedicalCenter()->value()])>9.?????????????????????</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border full-width details-table">
        <tbody class="text-center">
            <tr class="text-center" style="height: 36px;">
                <td rowspan="11" class="thin-width">??????????????????</td>
                <td style="width: 20%;">??????????????????</td>
                <td colspan="6" style="width: 14.2%;">?????????????????????</td>
                <td colspan="4" style="width: 9.46%;">?????????</td>
                <td colspan="2" style="width: 4.73%;">??????</td>
                <td colspan="6" style="width: 14.2%;">?????????????????????</td>
                <td colspan="2" class="font-x-small" style="width: 4.73%;">?????????<br>??????</td>
                <td colspan="6" style="width: 14.2%;">?????????????????????</td>
                <td>??????</td>
            </tr>
            @foreach($pdf->items as $item)
                <tr>
                    <td>{{ $item->serviceName }}</td>
                    {{-- ????????????????????? --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($item->serviceCode, $i, 1) === '' ? ' ' : mb_substr($item->serviceCode, $i, 1) }}</td>
                    @endfor
                    {{-- ????????? --}}
                    @for($i = 0; $i < 4; $i++)
                        <td>{{ mb_substr($item->unitScore, $i, 1) === '' ? ' ' : mb_substr($item->unitScore, $i, 1) }}</td>
                    @endfor
                    {{-- ?????? --}}
                    @for($i = 0; $i < 2; $i++)
                        <td>{{ mb_substr($item->count, $i, 1) === '' ? ' ' : mb_substr($item->count, $i, 1) }}</td>
                    @endfor
                    {{-- ????????????????????? --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($item->totalScore, $i, 1) === '' ? ' ' : mb_substr($item->totalScore, $i, 1) }}</td>
                    @endfor
                    {{-- ??????????????? --}}
                    @for($i = 0; $i < 2; $i++)
                        <td>{{ mb_substr($item->subsidyCount, $i, 1) === '' ? ' ' : mb_substr($item->subsidyCount, $i, 1) }}</td>
                    @endfor
                    {{-- ????????????????????? --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($item->subsidyScore, $i, 1) === '' ? ' ' : mb_substr($item->subsidyScore, $i, 1) }}</td>
                    @endfor
                    {{-- ?????? --}}
                    <td>{{ $item->note }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < $pdf->extraItemRows(); $i++)
                <tr>
                    <td></td>
                    {{-- ????????????????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ?????? --}}
                    <td></td>
                    <td></td>
                    {{-- ????????????????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ??????????????? --}}
                    <td></td>
                    <td></td>
                    {{-- ????????????????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ?????? --}}
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
                        <div>???<br>???<br>???<br>???<br>???<br>???</div>
                        <div>???<br>???<br>???<br>&#xFE36;</div>
                        <div>&#xFE35;<br>???<br>???<br>???<br>???<br>???</div>
                    </div>
                </td>
                <td style="width: 16%;">??????????????????</td>
                <td colspan="6" style="width: 14.2%;">?????????????????????</td>
                <td colspan="4" style="width: 9.46%;">?????????</td>
                <td colspan="2" style="width: 4.73%;">??????</td>
                <td colspan="6" style="width: 14.2%;">?????????????????????</td>
                <td colspan="2" class="font-x-small" style="width: 4.73%;">?????????<br>??????</td>
                <td colspan="6" style="width: 14.2%;">?????????????????????</td>
                <td class="font-x-small" style="width: 7%;">????????????<br>???????????????</td>
                <td>??????</td>
            </tr>
            @for($i = 0; $i < 3; $i++)
                <tr>
                    <td></td>
                    {{-- ????????????????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ?????? --}}
                    <td></td>
                    <td></td>
                    {{-- ????????????????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ??????????????? --}}
                    <td></td>
                    <td></td>
                    {{-- ????????????????????? --}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    {{-- ??????????????????????????? --}}
                    <td></td>
                    {{-- ?????? --}}
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <div class="flex">
        <div class="thick-border-top thick-border-left thick-border-bottom thin-width font-small flex align-center text-center">??????????????????</div>
        <table class="thick-border text-center totalling-table" style="width: calc(100% - 22px);">
            <tbody>
                <tr>
                    <td style="width: 18%;">??????????????????????????????<br>/?????????</td>
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
                    <td>????????????????????????</td>
                    @foreach($pdf->aggregates as $aggregate)
                        <td>{{ mb_substr($aggregate->serviceDays, 0, 1) }}</td>
                        <td>{{ mb_substr($aggregate->serviceDays, 1, 1) }}</td>
                        <td colspan="4" class="text-left font-x-small">???</td>
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td></td>
                        <td colspan="4" class="text-left font-x-small">???</td>
                    @endfor
                </tr>
                <tr>
                    <td>??????????????????</td>
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
                    <td>?????????????????????????????????</td>
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
                    <td>????????????????????????????????????</td>
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
                    <td colspan="6" class="font-x-small">?????????(/100)</td>
                </tr>
                <tr>
                    <td>??????????????????(???????????????????????????)+???</td>
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
                    <td colspan="3" class="font-x-small">??????</td>
                    @for($i = 0; $i < 3; $i++)
                        <td>{{ mb_substr($pdf->insuranceBenefitRate, $i, 1) === '' ? ' ' : mb_substr($pdf->insuranceBenefitRate, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>?????????????????????</td>
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
                    <td colspan="3" class="font-x-small">??????</td>
                    @for($i = 0; $i < 3; $i++)
                        <td>{{ $pdf->subsidyBenefitRate[$i + 1] }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>??????????????????</td>
                    @foreach($pdf->aggregates as $aggregate)
                        <td>{{ mb_substr($aggregate->insuranceUnitCost, 0, 1) }}</td>
                        <td class="triangle">{{ mb_substr($aggregate->insuranceUnitCost, 1, 1) }}</td>
                        <td>{{ mb_substr($aggregate->insuranceUnitCost, 2, 1) }}</td>
                        <td>{{ mb_substr($aggregate->insuranceUnitCost, 3, 1) }}</td>
                        <td colspan="2" class="font-x-small">???/??????</td>
                    @endforeach
                    @for($i = 0; $i < $pdf->extraAggregateColumns(); $i++)
                        <td></td>
                        <td class="triangle"></td>
                        <td></td>
                        <td></td>
                        <td colspan="2" class="font-x-small">???/??????</td>
                    @endfor
                    <td colspan="6" class="font-x-small">??????</td>
                </tr>
                <tr>
                    <td>??????????????????</td>
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
                    {{-- ?????? --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalInsuranceClaimAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalInsuranceClaimAmount, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>&#x246A;??????????????????</td>
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
                    {{-- ?????? --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalInsuranceCopayAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalInsuranceCopayAmount, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>&#x246B;???????????????</td>
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
                    {{-- ?????? --}}
                    @for($i = 0; $i < 6; $i++)
                        <td>{{ mb_substr($pdf->totalSubsidyClaimAmount, $i, 1) === '' ? ' ' : mb_substr($pdf->totalSubsidyClaimAmount, $i, 1) }}</td>
                    @endfor
                </tr>
                <tr>
                    <td>&#x246C;?????????????????????</td>
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
                    {{-- ?????? --}}
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
                <td rowspan="4" style="width: 8%;">???????????????????????????????????????</td>
                <td colspan="2">?????????</td>
                <td style="width: 24px;"></td>
                <td style="width: 24px;"></td>
                <td class="triangle" style="width: 24px;"></td>
                <td style="width: 24px;"></td>
                <td style="width: 6%;">%</td>
                <td colspan="6" class="font-x-small" style="width: 17%;">
                    <div class="flex justify-center text-left">????????????????????????<br>???????????????(???)</div>
                </td>
                <td colspan="6" style="width: 17%;">?????????(???)</td>
                <td colspan="6" class="font-x-small" style="width: 17%;">
                    <div class="flex justify-center text-left">??????????????????<br>?????????(???)</div>
                </td>
                <td style="width: 17%;">??????</td>
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
                {{-- TODO ???????????????????????? --}}
                <tr class="text-center">
                    <td></td>
                    <td>??????</td>
                    <td></td>
                    <td>??????</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
