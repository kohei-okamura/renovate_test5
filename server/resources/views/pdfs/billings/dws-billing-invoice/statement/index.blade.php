@push('css')
    <style>
    .statement.outer > *:nth-child(2) {
        margin: 4px 0;
    }
    .statement.outer > *:nth-child(n+4) {
        margin-top: 12px;
    }
    .statement .subtitle {
        font-size: 1rem;
        font-weight: normal;
    }
    .statement td:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    .statement tr {
        height: 24px;
    }
    .statement td {
        min-width: 22px;
    }
    .statement td.double {
        min-width: 44px;
    }
    .statement .service-type-table,
    .statement .details-table,
    .statement .totalling-table {
        font-size: 0.8rem;
    }
    .statement .details-table tr:first-of-type,
    .statement .totalling-table {
        border-right: solid 2px #000;
    }
    .statement .details-table tr:first-of-type > td:first-of-type,
    .statement .totalling-table tr:first-of-type > td:first-of-type {
        border-right: solid 2px #000;
    }
    .statement .details-table tr:not(:first-of-type) > td:first-of-type,
    .statement .details-table tr:not(:first-of-type) > td:last-of-type {
        text-align: left;
        padding: 0 6px;
    }
    .statement .details-table tr:first-of-type > td:not(:last-of-type),
    .statement .details-table tr:nth-of-type(n+1) > td:first-of-type,
    .statement .details-table tr:nth-of-type(n+1) > td:nth-of-type(7),
    .statement .details-table tr:nth-of-type(n+1) > td:nth-of-type(11),
    .statement .details-table tr:nth-of-type(n+1) > td:nth-of-type(14),
    .statement .details-table tr:nth-of-type(n+1) > td:nth-of-type(19) {
        border-right: solid 2px #000;
    }
    .statement .totalling-table tr.no-colspan > td:nth-last-of-type(7n),
    .statement .totalling-table tr:first-of-type > td:nth-last-of-type(3n+1),
    .statement .totalling-table tr:nth-of-type(2) > td:nth-last-of-type(3n),
    .statement .totalling-table tr:nth-of-type(4) > td:nth-last-of-type(5n+7) {
        border-left: solid 2px #000;
    }
    .statement .totalling-table tr:nth-last-of-type(2) {
        height: 2px;
    }
    .statement .special-benefit-table tr:first-of-type > td:not(:last-of-type),
    .statement .special-benefit-table tr:nth-of-type(2) > td:nth-of-type(4),
    .statement .special-benefit-table tr:nth-of-type(2) > td:nth-of-type(6),
    .statement .special-benefit-table tr:nth-of-type(2) > td:nth-of-type(11) {
        border-right: solid 2px #000;
    }
    </style>
@endpush

<div class="statement format-block">(????????????)</div>
<div class="statement outer flex column">
    <h1 class="title">????????????????????????????????????????????????</h1>
    <div class="subtitle text-center">
        <div>(?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????</div>
        <div>????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????)</div>
    </div>
    <div class="flex justify-between">
        <table class="thick-border">
            <tbody class="text-center">
                <tr>
                    <td>???????????????</td>
                    @foreach(str_split($statement->cityCode) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="padding: 0 8px;">?????????????????????</td>
                    @foreach(str_split($statement->subsidyCityCode) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
        <div>
            <table class="thick-border">
                <tbody>
                    <tr class="text-center">
                        <td style="padding: 0 12px;">{{ $statement->providedIn['japaneseCalender'] }}</td>
                        @foreach(str_split($statement->providedIn['year']) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td style="padding: 0 6px;">???</td>
                        @foreach(str_split($statement->providedIn['month']) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td style="padding: 0 6px;">??????</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="flex align-end">
            <table class="thick-border text-center">
                <tbody>
                    <tr>
                        <td style="padding: 0 5px;">??????????????????</td>
                        @foreach(str_split($statement->user->dwsNumber) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td style="padding: 0 5px;">????????????????????????<br>??????</td>
                        <td colspan="10" class="font-small">{{ $statement->user->name->displayName }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0 5px;">?????????????????????<br>???????????????</td>
                        <td colspan="10" class="font-small">{{ $statement->user->childName->displayName }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <table class="thick-border">
            <tbody class="text-center">
                <tr>
                    <td rowspan="4" class="thin-width">???????????????</td>
                    <td>?????????????????????</td>
                    @foreach(str_split($statement->office->code) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td rowspan="2" class="font-small">???????????????<br>???????????????<br>?????????</td>
                    <td colspan="10" class="font-small" style="height: 80px;">{{ $statement->office->name }}</td>
                </tr>
                <tr>
                    <td colspan="3">????????????</td>
                    <td colspan="7" class="font-small">{{ $statement->dwsAreaGradeName }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="font-x-small">??????????????????A????????????????????????????????????</td>
                    <td colspan="4" class="font-small">{{ $statement->exemptionMeasure }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="flex">
        <table class="thick-border">
            <tbody>
                <tr class="text-center">
                    <td style="padding: 0 8px;">??????????????????????????? ???</td>
                    @foreach(str_split($statement->copayLimit) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td style="padding: 0 8px;">??????????????????A??????????????????</td>
                    <td class="double">{{ $statement->exemptionTarget }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="thick-border full-width">
        <tbody class="text-center">
            <tr class="thick-border-bottom">
                <td rowspan="2" class="thick-border-right">????????????????????????<br>???????????????</td>
                <td colspan="2">?????????????????????</td>
                @foreach(str_split($statement->copayCoordination['code']) as $digit)
                    <td>{{ $digit }}</td>
                @endforeach
                <td class="thick-border-left">????????????</td>
                <td>{{ $statement->copayCoordination['result'] }}</td>
                <td class="thick-border-left">???????????????</td>
                @foreach(str_split($statement->copayCoordination['amount']) as $digit)
                    <td>{{ $digit }}</td>
                @endforeach
            </tr>
            <tr>
                <td>???????????????</td>
                <td style="border: 0;"></td>
                <td colspan="18" class="text-left">{{ $statement->copayCoordination['name'] }}</td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border full-width service-type-table">
        <tbody class="text-center">
            <tr>
                <td rowspan="3">????????????<br>??????</td>
                @foreach($statement->daysRecords as $daysRecord)
                    @foreach(str_split($daysRecord['dwsServiceDivisionCode']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???????????????</td>
                    <td>{{ $daysRecord['startedOn']['japaneseCalender'] }}</td>
                    @foreach(str_split($daysRecord['startedOn']['year']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???</td>
                    @foreach(str_split($daysRecord['startedOn']['month']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???</td>
                    @foreach(str_split($daysRecord['startedOn']['day']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???</td>
                    <td>???????????????</td>
                    <td>{{ $daysRecord['terminatedOn']['japaneseCalender'] }}</td>
                    @foreach(str_split($daysRecord['terminatedOn']['year']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???</td>
                    @foreach(str_split($daysRecord['terminatedOn'] ['month']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???</td>
                    @foreach(str_split($daysRecord['terminatedOn']['day']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>???</td>
                    <td>????????????</td>
                    @foreach(str_split($daysRecord['serviceDays']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>????????????</td>
                    <td></td>{{-- ??????????????????????????????????????????????????? --}}
                    <td></td>
                @endforeach
            </tr>
            @for($i = 0; $i < $statement->extraDaysRecordRows(); $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td>???????????????</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>???</td>
                    <td></td>
                    <td></td>
                    <td>???</td>
                    <td></td>
                    <td></td>
                    <td>???</td>
                    <td>???????????????</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>???</td>
                    <td></td>
                    <td></td>
                    <td>???</td>
                    <td></td>
                    <td></td>
                    <td>???</td>
                    <td>????????????</td>
                    <td></td>
                    <td></td>
                    <td>????????????</td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <table class="thick-border full-width details-table">
        <tbody class="text-center">
            <tr class="text-center thick-border-bottom">
                <td rowspan="14" class="thin-width">??????????????????</td>
                <td style="width: 24.3%;">??????????????????</td>
                <td colspan="6">?????????????????????</td>
                <td colspan="4">?????????</td>
                <td colspan="3">??????</td>
                <td colspan="5">?????????????????????</td>
                <td style="width: 27%;">??????</td>
            </tr>
            @foreach($statement->itemsInThePage($page) as $item)
                <tr>
                    <td>{{ $item->serviceName }}</td>
                    @foreach(str_split($item->serviceCode) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    @foreach(str_split($item->unitScore) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    @foreach(str_split($item->count) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    @foreach(str_split($item->totalScore) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td></td>
                </tr>
            @endforeach
            @for($i = 0; $i < $statement->extraItemRows($page); $i++)
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
        </tbody>
    </table>
    <table class="thick-border full-width text-center totalling-table">
        <tbody>
            <tr>
                <td rowspan="16" class="thin-width">??????????????????</td>
                <td colspan="2" style="width: 18.9%;">???????????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->serviceDivisionCode) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td colspan="5">{{ $aggregate->resolvedServiceDivisionCode }}</td>
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td colspan="5"></td>
                @endfor
                <td colspan="7" rowspan="2">??????</td>
            </tr>
            <tr>
                <td colspan="2">????????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->serviceDays) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td colspan="5" class="text-left">???</td>
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td colspan="5" class="text-left">???</td>
                @endfor
            </tr>
            <tr class="no-colspan">
                <td colspan="2">???????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->subtotalScore) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalScore) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr>
                <td colspan="2">???????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->unitCost) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td colspan="3" class="font-x-small">???/??????</td>
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="3" class="font-x-small">???/??????</td>
                @endfor
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
            </tr>
            <tr class="no-colspan thick-border-bottom">
                <td colspan="2">????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->subtotalFee) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalFee) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr class="no-colspan">
                <td colspan="2">1????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->unmanagedCopay) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
            </tr>
            <tr class="no-colspan">
                <td colspan="2">?????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->managedCopay) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
            </tr>
            <tr class="no-colspan thick-border-bottom">
                <td colspan="2" class="font-x-small">??????????????????(????????????????????????)</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->cappedCopay) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalCappedCopay) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr class="no-colspan">
                <td rowspan="2">A?????????</td>
                <td>??????????????????</td>
                @for($i = 0; $i < 5; $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
            </tr>
            <tr class="no-colspan thick-border-bottom">
                <td class="font-x-small">???????????????????????????</td>
                @for($i = 0; $i < 5; $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
            </tr>
            <tr class="no-colspan thick-border-bottom">
                <td colspan="2">???????????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->adjustedCopay) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalAdjustedCopay) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr class="no-colspan thick-border-bottom">
                <td colspan="2" class="font-x-small">????????????????????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->coordinatedCopay) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalCoordinatedCopay) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr class="no-colspan thick-border-bottom">
                <td colspan="2">????????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->subtotalCopay) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalCopay) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr class="no-colspan">
                <td>?????????</td>
                <td>?????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->subtotalBenefit) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalBenefit) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
            <tr>
                <td colspan="37"></td>
            </tr>
            <tr class="no-colspan">
                <td colspan="2">???????????????????????????</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->subtotalSubsidy) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endfor
                {{-- ?????? --}}
                @if($statement->isLastPage($page))
                    @foreach(str_split($statement->totalSubsidy) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                @else
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                @endif
            </tr>
        </tbody>
    </table>
    <div class="flex justify-between">
        <table class="thick-border special-benefit-table">
            <tbody>
                <tr class="text-center">
                    <td rowspan="2" style="padding: 0 4px;">??????????????????????????????</td>
                    <td colspan="4">????????????</td>
                    <td colspan="2">??????</td>
                    <td colspan="5">??????????????????</td>
                    <td colspan="5">???????????????</td>
                </tr>
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
                </tr>
            </tbody>
        </table>
        <div class="flex align-end">
            <table class="thick-border">
                <tbody>
                    <tr class="text-center">
                    @if($statement->pages() > 1)
                        <td>{{ mb_substr(\Domain\Billing\DwsBillingStatementPdf::formatedPage($statement->pages()), 0, 1) }}</td>
                        <td>{{ mb_substr(\Domain\Billing\DwsBillingStatementPdf::formatedPage($statement->pages()), 1, 1) }}</td>
                        <td>??????</td>
                        <td>{{ mb_substr(\Domain\Billing\DwsBillingStatementPdf::formatedPage($page), 0, 1) }}</td>
                        <td>{{ mb_substr(\Domain\Billing\DwsBillingStatementPdf::formatedPage($page), 1, 1) }}</td>
                        <td>??????</td>
                    @else
                        <td></td>
                        <td></td>
                        <td>??????</td>
                        <td></td>
                        <td></td>
                        <td>??????</td>
                    @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
