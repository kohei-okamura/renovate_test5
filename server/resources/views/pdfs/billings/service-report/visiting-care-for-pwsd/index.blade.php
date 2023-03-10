@push('css')
    <style>
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(3),
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(4),
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(5),
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(7),
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(8),
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(13),
    .visiting-care-for-pwsd .main-table > thead > tr:first-of-type > td:nth-of-type(14),
    .visiting-care-for-pwsd .main-table > thead > tr:nth-of-type(2) > td:nth-of-type(3),
    .visiting-care-for-pwsd .main-table > thead > tr:nth-of-type(2) > td:nth-of-type(7),
    .visiting-care-for-pwsd .main-table > thead > tr:nth-of-type(3) > td:nth-of-type(2),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(3),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(4),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(8),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(12),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(13),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(18),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(n+4) > td:nth-of-type(19),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(-n+2) > td:nth-of-type(3),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(-n+2) > td:nth-of-type(7),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(-n+2) > td:nth-of-type(8),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(-n+2) > td:nth-of-type(13),
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(-n+2) > td:nth-of-type(14) {
        border-right: solid 2px #000;
    }
    .visiting-care-for-pwsd .main-table > tbody > tr:nth-last-of-type(3) {
        height: 2px;
    }
    </style>
@endpush

<div class="visiting-care-for-pwsd outer flex column">
    <div class="flex justify-end">
        <span class="format-block">(??????3-1)</span>
    </div>
    <div class="title-block">
        <span class="date">{{ $pdf->providedIn->formatLocalized('%EC') }}<span class="year">{{ $pdf->providedIn->formatLocalized('%Ey') }}</span>???<span class="month">{{$pdf->providedIn->format('n')}}</span>??????</span>
        <h1 class="title">???????????????????????????????????????????????????</h1>
    </div>
    <div class="flex full-width font-small">
        <table class="thick-border-top thick-border-bottom thick-border-left text-center user-table" style="width: 65%;">
            <tbody>
                <tr>
                    <td style="width: 15%; padding: 8px 0">????????????<br>??????</td>
                    @foreach(str_split($pdf->user->dwsNumber) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td style="width: 27%">???????????????????????????</td>
                    <td style="width: 35%">{{ $pdf->user->name->displayName }}</td>
                </tr>
                <tr>
                    <td style="padding: 16px 0">???????????????</td>
                    <td colspan="12" class="text-left" style="padding: 0 4px;">
                        @foreach($pdf->grantAmounts as $grantAmount)
                            {{ $grantAmount }}<br>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="flex column thick-border-top thick-border-right thick-border-bottom thick-border-left" style="width: 35%;">
            <div class="flex lattice table-like thin-border-bottom" style="height: 2rem; line-height: 2rem;">
                <span class="flexible">???????????????</span>
                @foreach(str_split($pdf->office->code) as $digit)
                    <span>{{ $digit }}</span>
                @endforeach
            </div>
            <div class="flex flexible lattice">
                <span class="flex align-center" style="padding: 0 12px;">???????????????<br>???????????????</span>
                <span class="flexible flex align-center" style="padding: 0 4px;">{{ $pdf->office->name }}</span>
            </div>
        </div>
        <div>
        </div>
    </div>
    <table class="thick-border text-center full-width main-table">
        <thead>
            <tr>
                <td rowspan="3" style="width: 24px;">???<br>???</td>
                <td rowspan="3" style="width: 24px;">???<br>???</td>
                <td rowspan="3" style="width: 20px;"></td>
                <td rowspan="3" class="font-x-small" style="width: 72px;">??????????????????<br>?????????</td>
                <td colspan="4">????????????????????????</td>
                <td colspan="2" class="font-x-small">????????????????????????</td>
                <td colspan="2">???????????????</td>
                <td rowspan="3" style="width: 24px;">???<br>???<br>???<br>???</td>
                <td rowspan="3" style="width: 24px;">???<br>???<br>???<br>???</td>
                <td rowspan="3" style="width: 48px;">??????<br>??????</td>
                <td rowspan="3" style="width: 48px;">?????????<br>??????<br>??????</td>
                <td rowspan="3" class="font-x-small" style="width: 48px;">?????????<br>?????????<br>??????<br>??????</td>
                <td rowspan="3" class="font-x-small" style="width: 48px;">?????????<br>?????????<br>?????????<br>??????</td>
                <td rowspan="3" style="width: 54px;">?????????<br>?????????</td>
                <td rowspan="3">??????</td>
            </tr>
            <tr>
                <td rowspan="2" style="width: 50px;">??????<br>??????</td>
                <td rowspan="2" style="width: 50px;">??????<br>??????</td>
                <td colspan="2">???????????????</td>
                <td rowspan="2" style="width: 50px;">??????<br>??????</td>
                <td rowspan="2" style="width: 50px;">??????<br>??????</td>
                <td rowspan="2" style="width: 48px;">??????</td>
                <td rowspan="2" style="width: 48px;">??????</td>
            </tr>
            <tr>
                <td style="width: 48px;">??????</td>
                <td style="width: 48px;">??????</td>
            </tr>
        </thead>
        <tbody>
            @foreach($pdf->items as $item)
                <tr>
                    <td>{{ $item->providedOn }}</td>
                    <td>{{ $item->weekday }}</td>
                    <td>{{ $item->serviceCount }}</td>
                    <td @class(['font-x-small' => mb_strlen($item->situation) > 5])>{{ $item->situation }}</td>
                    <td>{{ $item->plan->start }}</td>
                    <td>{{ $item->plan->end }}</td>
                    <td>{{ $item->plan->serviceDurationHours }}</td>
                    <td>{{ $item->plan->movingDurationHours }}</td>
                    <td>{{ $item->result->start }}</td>
                    <td>{{ $item->result->end }}</td>
                    <td>{{ $item->result->serviceDurationHours }}</td>
                    <td>{{ $item->result->movingDurationHours }}</td>
                    <td>{{ $item->headcount }}</td>
                    <td></td>
                    <td>{{ $item->isFirstTime }}</td>
                    <td>{{ $item->isEmergency }}</td>
                    <td>{{ $item->isWelfareSpecialistCooperation }}</td>
                    <td>{{ $item->isMovingCareSupport }}</td>
                    <td></td> {{-- ?????????????????? --}}
                    <td>{{ $item->note }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < $pdf->extraItemRows(); $i++)
                <tr>
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
                    <td></td>
                </tr>
            @endfor
            <tr>
                <td colspan="20"></td>
            </tr>
            <tr>
                <td colspan="6">???????????????</td>
                <td class="no-use"></td>
                <td>{{ $pdf->plan->outingSupportForPwsd }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->outingSupportForPwsd }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
            </tr>
            <tr>
                <td colspan="6">??????</td>
                <td>{{ $pdf->plan->visitingCareForPwsd }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->visitingCareForPwsd }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="right-justify">{{ $pdf->firstTimeCount }}<span class="font-x-small">???</span></td>
                <td class="right-justify">{{ $pdf->emergencyCount }}<span class="font-x-small">???</span></td>
                <td class="right-justify">{{ $pdf->behavioralDisorderSupportCooperationCount }}<span class="font-x-small">???</span></td>
                <td class="right-justify">{{ $pdf->movingCareSupportCount }}<span class="font-x-small">???</span></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
            </tr>
        </tbody>
    </table>
    <div class="flex justify-end">
        <table class="thick-border page-table">
            <tbody>
                <tr class="text-center">
                    <td>{{ $pdf->maxPageCount }}</td>
                    <td>??????</td>
                    <td>{{ $pdf->currentPageCount }}</td>
                    <td>???</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
