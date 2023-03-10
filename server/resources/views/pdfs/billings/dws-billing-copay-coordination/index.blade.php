@php
    $now = \Domain\Billing\DwsBillingCopayCoordinationPdf::localized(\Domain\Common\Carbon::now())
@endphp

@extends('pdfs.base')

@push('css')
    <style>
    body {
        margin: 0;
    }
    .dws-billing-copay-coordination.outer {
        border: solid 1px #000;
        height: 100%;
        padding: 14px 12px 14px 18px;
        width: 100%;
    }
    .dws-billing-copay-coordination.outer > *:nth-child(n+2) {
        margin-top: 12px;
    }
    .dws-billing-copay-coordination .title {
        font-size: 1.3rem;
        text-align: center;
    }
    .dws-billing-copay-coordination table {
        font-size: 0.9rem;
    }
    .dws-billing-copay-coordination .thin-width {
        width: 1rem;
    }
    .dws-billing-copay-coordination td:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    .dws-billing-copay-coordination td {
        min-width: 22px;
    }
    .dws-billing-copay-coordination .main-table {
        font-size: 0.9rem;
    }
    .dws-billing-copay-coordination .main-table tr:not(:nth-of-type(3)) {
        height: 28px;
    }
    .dws-billing-copay-coordination .main-table tr:first-of-type td:nth-of-type(n+3) {
        width: 16%;
    }
    .dws-billing-copay-coordination .main-table tr:first-of-type {
        border-bottom: solid 2px #000;
    }
    .dws-billing-copay-coordination .main-table tr:last-of-type {
        border-top: solid 2px #000;
    }
    .dws-billing-copay-coordination .main-table td:first-of-type {
        border-right: solid 2px #000;
    }
    .dws-billing-copay-coordination .main-table tr:first-of-type td:nth-of-type(n+3),
    .dws-billing-copay-coordination .main-table tr:nth-of-type(n+2) td:not(:first-of-type) {
        border-left: solid 2px #000;
    }
    .dws-billing-copay-coordination .main-table tr:nth-of-type(3) td:nth-of-type(n+2) {
        font-size: 0.8rem;
    }
    .dws-billing-copay-coordination .main-table tr:nth-of-type(n+4) td:nth-of-type(n+2) {
        padding-right: 8px;
        text-align: right;
    }
    .dws-billing-copay-coordination .date {
        margin-top: 18px;
        margin-left: 6%;
    }
    .dws-billing-copay-coordination .year,
    .dws-billing-copay-coordination .month,
    .dws-billing-copay-coordination .day {
        display: inline-block;
        min-width: 32px;
    }
    </style>
@endpush

@section('title', '???????????????????????????????????????')

@section('content')
    @foreach($bundles as $bundle)
        @foreach($bundle['copayCoordinations'] as $copayCoordination)
            <section class="sheet">
                <div class="dws-billing-copay-coordination outer flex column">
                    <h1 class="title">???????????????????????????????????????</h1>
                    <div class="flex justify-end">
                        <table class="thick-border">
                            <tbody>
                                <tr class="text-center">
                                    <td style="padding: 0 12px;">{{ $copayCoordination->providedIn['japaneseCalender'] }}</td>
                                    <td>{{ mb_substr($copayCoordination->providedIn['year'], 0, 1) }}</td>
                                    <td>{{ mb_substr($copayCoordination->providedIn['year'], 1, 1) }}</td>
                                    <td style="padding: 0 6px;">???</td>
                                    <td>{{ mb_substr($copayCoordination->providedIn['month'], 0, 1) }}</td>
                                    <td>{{ mb_substr($copayCoordination->providedIn['month'], 1, 1) }}</td>
                                    <td style="padding: 0 6px;">??????</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between">
                        <div class="flex align-end">
                            <table class="thick-border text-center">
                                <tbody>
                                    <tr class="fon-small">
                                        <td>???????????????</td>
                                        @for($i = 0; $i < 6; $i++)
                                            <td>{{ mb_substr($copayCoordination->cityCode, $i, 1) }}</td>
                                        @endfor
                                        <td colspan="4"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0 5px;">??????????????????</td>
                                        @for($i = 0; $i < 10; $i++)
                                            <td>{{ mb_substr($copayCoordination->user->dwsNumber, $i, 1) }}</td>
                                        @endfor
                                    </tr>
                                    <tr>
                                        <td style="padding: 0 5px;">????????????????????????<br>??????</td>
                                        <td colspan="10" class="font-small">{{ $copayCoordination->user->name->displayName }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0 5px;">?????????????????????<br>???????????????</td>
                                        <td colspan="10" class="font-small">{{ $copayCoordination->user->childName->displayName }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <table class="thick-border">
                            <tbody class="text-center">
                                <tr>
                                    <td rowspan="2" class="thin-width">???????????????</td>
                                    <td class="font-small" style="padding: 0 8px;">?????????????????????</td>
                                    @for($i = 0; $i < 10; $i++)
                                        <td>{{ mb_substr($copayCoordination->office->code, $i, 1) }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td>???????????????<br>???????????????<br>?????????</td>
                                    <td colspan="10" class="font-small" style="height: 100px;">{{ $copayCoordination->office->name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex">
                        <table class="thick-border">
                            <tbody>
                                <tr class="text-center">
                                    <td style="padding: 0 8px;">???????????????????????????</td>
                                    @for($i = 0; $i < 5; $i++)
                                        <td>{{ mb_substr(sprintf('% 5d', $copayCoordination->user->copayLimit), $i, 1) }}</td>
                                    @endfor
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 24px;">
                        <div class="flex text-center" style="height: 28px;">
                            <span class="thick-border-left thick-border-top" style="padding: 0 12px;">????????????????????????????????????</span>
                            <span class="thick-border-left thick-border-top thick-border-left thick-border-right" style="width: 6%">{{ $copayCoordination->result->value() }}</span>
                        </div>
                        <div class="thick-border" style="padding-top: 8px; padding-bottom: 8px; padding-left: 6%; line-height: 1.6rem">
                            1 ???????????????????????????????????????????????????????????????????????????????????????????????????????????????<br>
                            2 ??????????????????????????????????????????????????????????????????????????????????????????????????????<br>
                            3 ???????????????????????????????????????????????????????????????????????????????????????????????????????????????
                        </div>
                    </div>
                    <table class="thick-border full-width text-center main-table">
                        <tbody>
                            <tr>
                                <td rowspan="6" class="thin-width font-x-small" style="padding: 8px 0;">???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???</td>
                                <td>??????</td>
                                @for($i = 0; $i < 5; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : $copayCoordination->items[$i]->itemNumber }}</td>
                                @endfor
                            </tr>
                            <tr>
                                <td>???????????????</td>
                                @for($i = 0; $i < 5; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : $copayCoordination->items[$i]->officeCode }}</td>
                                @endfor
                            </tr>
                            <tr style="height: 72px;">
                                <td>???????????????</td>
                                @for($i = 0; $i < 5; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : $copayCoordination->items[$i]->officeName }}</td>
                                @endfor
                            </tr>
                            <tr>
                                <td>????????????</td>
                                @for($i = 0; $i < 5; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : number_format($copayCoordination->items[$i]->fee) }}</td>
                                @endfor
                            </tr>
                            <tr>
                                <td>??????????????????</td>
                                @for($i = 0; $i < 5; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : number_format($copayCoordination->items[$i]->copay) }}</td>
                                @endfor
                            </tr>
                            <tr>
                                <td>?????????????????????????????????</td>
                                @for($i = 0; $i < 5; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : number_format($copayCoordination->items[$i]->coordinatedCopay) }}</td>
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                    <table class="thick-border full-width text-center main-table">
                        <tbody>
                            <tr>
                                <td rowspan="6" class="thin-width font-x-small" style="padding: 8px 0;">???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???<br>???</td>
                                <td>??????</td>
                                @for($i = 5; $i < 9; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : $copayCoordination->items[$i]->itemNumber }}</td>
                                @endfor
                                <td rowspan="3" class="thick-border-bottom">??????</td>
                            </tr>
                            <tr>
                                <td>???????????????</td>
                                @for($i = 5; $i < 9; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : $copayCoordination->items[$i]->officeCode }}</td>
                                @endfor
                            </tr>
                            <tr style="height: 72px;">
                                <td>???????????????</td>
                                @for($i = 5; $i < 9; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : $copayCoordination->items[$i]->officeName }}</td>
                                @endfor
                            </tr>
                            <tr>
                                <td>????????????</td>
                                @for($i = 5; $i < 9; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : number_format($copayCoordination->items[$i]->fee) }}</td>
                                @endfor
                                <td>{{ empty($copayCoordination->total) ? ' ' : number_format($copayCoordination->total->fee) }}</td>
                            </tr>
                            <tr>
                                <td>??????????????????</td>
                                @for($i = 5; $i < 9; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : number_format($copayCoordination->items[$i]->copay) }}</td>
                                @endfor
                                <td>{{ empty($copayCoordination->total) ? ' ' : number_format($copayCoordination->total->copay) }}</td>
                            </tr>
                            <tr>
                                <td>?????????????????????????????????</td>
                                @for($i = 5; $i < 9; $i++)
                                    <td>{{ empty($copayCoordination->items[$i]) ? ' ' : number_format($copayCoordination->items[$i]->coordinatedCopay) }}</td>
                                @endfor
                                <td>{{ empty($copayCoordination->total) ? ' ' : number_format($copayCoordination->total->coordinatedCopay) }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center" style="margin-top: 36px;">?????????????????????????????????????????????</div>
                    <div class="date text-center">
                        <span>{{ $now['japaneseCalender'] }}</span>
                        <span class="year">{{ intval($now['year']) }}</span>
                        <span>???</span>
                        <span class="month">{{ intval($now['month']) }}</span>
                        <span>???</span>
                        <span class="day">{{ intval($now['day']) }}</span>
                        <span>???</span>
                    </div>
                    <div class="flex justify-end" style="margin-right: 6%; margin-bottom: 2%;">
                        <span>??????????????????????????????</span>
                        <span style="min-width: 24%;"></span>
                        <span>???</span>
                    </div>
                </div>
            </section>
        @endforeach
    @endforeach
@endsection
