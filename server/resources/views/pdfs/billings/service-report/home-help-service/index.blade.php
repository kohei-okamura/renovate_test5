@push('css')
    <style>
    .home-help-service .main-table > tbody > tr:nth-last-of-type(6) > td:nth-of-type(4),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(-n+4) > td:nth-of-type(7) {
        border-right: solid 1px #000;
    }
    .home-help-service .main-table > tbody > tr:first-of-type {
        border-top: solid 2px #000;
    }
    .home-help-service .main-table > thead > tr:first-of-type > td:nth-of-type(3),
    .home-help-service .main-table > thead > tr:first-of-type > td:nth-of-type(4),
    .home-help-service .main-table > thead > tr:first-of-type > td:nth-of-type(5),
    .home-help-service .main-table > thead > tr:first-of-type > td:nth-of-type(7),
    .home-help-service .main-table > thead > tr:first-of-type > td:nth-of-type(11),
    .home-help-service .main-table > thead > tr:nth-of-type(2) > td:nth-of-type(3),
    .home-help-service .main-table > thead > tr:nth-of-type(2) > td:nth-of-type(7),
    .home-help-service .main-table > thead > tr:nth-of-type(3) > td:nth-of-type(2),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(n+8) > td:nth-of-type(3),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(n+8) > td:nth-of-type(4),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(n+8) > td:nth-of-type(8),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(n+8) > td:nth-of-type(12),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(n+8) > td:nth-of-type(16),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(7) > td:nth-of-type(1),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(7) > td:nth-of-type(3),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(7) > td:nth-of-type(5),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(7) > td:nth-of-type(7),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(5) > td:nth-of-type(1),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(5) > td:nth-of-type(4),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(5) > td:nth-of-type(10),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(5) > td:nth-of-type(13),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(1) > td:nth-of-type(3),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(1) > td:nth-of-type(9) {
        border-right: solid 2px #000;
    }
    .home-help-service .main-table > tbody > tr:nth-last-of-type(8) {
        height: 2px;
    }
    .home-help-service .main-table > tbody > tr:nth-last-of-type(1) > td:nth-of-type(n+4):nth-of-type(-n+7),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(n+2):nth-last-of-type(-n+4) > td:nth-of-type(n+3):nth-of-type(-n+6),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(5) > td:nth-of-type(n+5):nth-of-type(-n+8),
    .home-help-service .main-table > tbody > tr:nth-last-of-type(5) > td:nth-of-type(n+11):nth-of-type(-n+13) {
        font-size: 0.7rem;
    }
    </style>
@endpush

<div class="home-help-service outer flex column">
    <div class="flex justify-end">
        <span class="format-block">(??????1)</span>
    </div>
    <div class="title-block">
        <span class="date">{{ $pdf->providedIn->formatLocalized('%EC') }}<span class="year">{{ $pdf->providedIn->formatLocalized('%Ey') }}</span>???<span class="month">{{$pdf->providedIn->format('n')}}</span>??????</span>
        <h1 class="title">?????????????????????????????????????????????</h1>
    </div>
    <div class="flex full-width font-small">
        <table class="thick-border-top thick-border-bottom thick-border-left text-center user-table" style="width: 65%;">
            <tbody>
                <tr>
                    <td style="width: 15%; padding: 8px 0">????????????<br>??????</td>
                    @foreach(str_split($pdf->user->dwsNumber) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td style="width: 27%">???????????????????????????<br>?????????????????????</td>
                    <td style="width: 35%">
                        {{ $pdf->user->name->displayName }}
                        @if (strlen(trim($pdf->user->childName->displayName)) > 0 )
                            <br>???{{ $pdf->user->childName->displayName }}???
                        @endif
                    </td>
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
                <td rowspan="3" class="font-x-small" style="width: 96px;">????????????<br>??????</td>
                <td colspan="4">??????????????????</td>
                <td colspan="4" class="font-x-small">????????????????????????</td>
                <td colspan="2">???????????????</td>
                <td rowspan="3" style="width: 24px;">???<br>???<br>???<br>???</td>
                <td rowspan="3" style="width: 32px;">???<br>???<br>???<br>???</td>
                <td rowspan="3" style="width: 48px;">?????????<br>??????<br>??????</td>
                <td rowspan="3" class="font-x-small" style="width: 48px;">??????<br>??????<br>?????????<br>??????</td>
                <td rowspan="3" style="width: 56px;">?????????<br>?????????</td>
                <td rowspan="3">??????</td>
            </tr>
            <tr>
                <td rowspan="2" style="width: 56px;">??????<br>??????</td>
                <td rowspan="2" style="width: 56px;">??????<br>??????</td>
                <td colspan="2">???????????????</td>
                <td colspan="2" rowspan="2">??????<br>??????</td>
                <td colspan="2" rowspan="2">??????<br>??????</td>
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
                    <td></td>
                    <td class="@if(mb_strlen($item->serviceType) > 9) font-xx-small @elseif(mb_strlen($item->serviceType) > 5) font-x-small @endif">{{ $item->serviceType }}</td>
                    <td>{{ $item->plan->start }}</td>
                    <td>{{ $item->plan->end }}</td>
                    <td>{{ $item->plan->serviceDurationHours }}</td>
                    <td>{{ $item->plan->movingDurationHours }}</td>
                    <td colspan="2">{{ $item->result->start }}</td>
                    <td colspan="2">{{ $item->result->end }}</td>
                    <td>{{ $item->result->serviceDurationHours }}</td>
                    <td>{{ $item->result->movingDurationHours }}</td>
                    <td>{{ $item->headcount }}</td>
                    <td>{{ $item->isFirstTime }}</td>
                    <td>{{ $item->isEmergency }}</td>
                    <td>{{ $item->isWelfareSpecialistCooperation }}</td>
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
                    <td colspan="2"></td>
                    <td colspan="2"></td>
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
                <td rowspan="2"></td>
                <td colspan="5" rowspan="2"></td>
                <td colspan="2" rowspan="2">??????<br>????????????</td>
                <td colspan="4">??????(???????????????)</td>
                <td colspan="2" rowspan="2">??????<br>????????????</td>
                <td rowspan="7" class="no-use"></td>
                <td colspan="3" rowspan="2" class="no-use"></td>
                <td colspan="2" rowspan="7" class="no-use"></td>
            </tr>
            <tr class="font-x-small">
                <td style="width: 28px;">100%</td>
                <td style="width: 28px;">90%</td>
                <td style="width: 28px;">70%</td>
                <td style="width: 28px;">??????</td>
            </tr>
            <tr>
                <td rowspan="5">???<br>???</td>
                <td colspan="5">??????????????????????????????</td>
                <td>{{ $pdf->plan->physicalCare }}</td>
                <td rowspan="4" class="no-use"></td>
                <td>{{ $pdf->result->physicalCare100 }}</td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->physicalCare70 }}</td>
                <td>{{ $pdf->result->physicalCarePwsd }}</td>
                <td>{{ $pdf->result->physicalCare }}</td>
                <td rowspan="4" class="no-use"></td>
                <td rowspan="5" class="right-justify">{{ $pdf->firstTimeCount }}<span>???</span></td>
                <td rowspan="5" class="right-justify">{{ $pdf->emergencyCount }}<span>???</span></td>
                <td rowspan="5" class="right-justify">{{ $pdf->welfareSpecialistCooperationCount }}<span>???</span></td>
            </tr>
            <tr>
                <td colspan="5">????????????(?????????????????????)</td>
                <td>{{ $pdf->plan->accompanyWithPhysicalCare }}</td>
                <td>{{ $pdf->result->accompanyWithPhysicalCare100 }}</td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->accompanyWithPhysicalCare70 }}</td>
                <td>{{ $pdf->result->accompanyWithPhysicalCarePwsd }}</td>
                <td>{{ $pdf->result->accompanyWithPhysicalCare }}</td>
            </tr>
            <tr>
                <td colspan="5">????????????</td>
                <td>{{ $pdf->plan->housework }}</td>
                <td>{{ $pdf->result->housework100 }}</td>
                <td>{{ $pdf->result->housework90 }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->housework }}</td>
            </tr>
            <tr>
                <td colspan="5">????????????(???????????????????????????)</td>
                <td>{{ $pdf->plan->accompany }}</td>
                <td>{{ $pdf->result->accompany100 }}</td>
                <td>{{ $pdf->result->accompany90 }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->accompany }}</td>
            </tr>
            <tr>
                <td colspan="5">?????????????????????</td>
                <td class="no-use"></td>
                <td>{{ $pdf->plan->accessibleTaxi }}</td>
                <td>{{ $pdf->result->accessibleTaxi100 }}</td>
                <td>{{ $pdf->result->accessibleTaxi90 }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->accessibleTaxi }}</td>
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
