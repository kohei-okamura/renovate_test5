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

<div class="statement format-block">(様式第二)</div>
<div class="statement outer flex column">
    <h1 class="title">介護給付費・訓練等給付費等明細書</h1>
    <div class="subtitle text-center">
        <div>(居宅介護、重度訪問介護、同行援護、行動援護、重度障害者等包括支援、短期入所、療養介護、</div>
        <div>生活介護、施設入所支援、自立訓練、就労移行支援、就労継続支援、就労定着支援、自立生活援助)</div>
    </div>
    <div class="flex justify-between">
        <table class="thick-border">
            <tbody class="text-center">
                <tr>
                    <td>市町村番号</td>
                    @foreach(str_split($statement->cityCode) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td style="padding: 0 8px;">助成自治体番号</td>
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
                        <td style="padding: 0 6px;">年</td>
                        @foreach(str_split($statement->providedIn['month']) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td style="padding: 0 6px;">月分</td>
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
                        <td style="padding: 0 5px;">受給者証番号</td>
                        @foreach(str_split($statement->user->dwsNumber) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td style="padding: 0 5px;">支給決定障害者等<br>氏名</td>
                        <td colspan="10" class="font-small">{{ $statement->user->name->displayName }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0 5px;">支給決定に係る<br>障害児氏名</td>
                        <td colspan="10" class="font-small">{{ $statement->user->childName->displayName }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <table class="thick-border">
            <tbody class="text-center">
                <tr>
                    <td rowspan="4" class="thin-width">請求事業者</td>
                    <td>指定事業所番号</td>
                    @foreach(str_split($statement->office->code) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td rowspan="2" class="font-small">事業者及び<br>その事業所<br>の名称</td>
                    <td colspan="10" class="font-small" style="height: 80px;">{{ $statement->office->name }}</td>
                </tr>
                <tr>
                    <td colspan="3">地域区分</td>
                    <td colspan="7" class="font-small">{{ $statement->dwsAreaGradeName }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="font-x-small">就労継続支援A型事業者負担減免措置実施</td>
                    <td colspan="4" class="font-small">{{ $statement->exemptionMeasure }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="flex">
        <table class="thick-border">
            <tbody>
                <tr class="text-center">
                    <td style="padding: 0 8px;">利用者負担上限月額 ①</td>
                    @foreach(str_split($statement->copayLimit) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td style="padding: 0 8px;">就労継続支援A型減免対象者</td>
                    <td class="double">{{ $statement->exemptionTarget }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="thick-border full-width">
        <tbody class="text-center">
            <tr class="thick-border-bottom">
                <td rowspan="2" class="thick-border-right">利用者負担上限額<br>管理事業所</td>
                <td colspan="2">指定事業所番号</td>
                @foreach(str_split($statement->copayCoordination['code']) as $digit)
                    <td>{{ $digit }}</td>
                @endforeach
                <td class="thick-border-left">管理結果</td>
                <td>{{ $statement->copayCoordination['result'] }}</td>
                <td class="thick-border-left">管理結果額</td>
                @foreach(str_split($statement->copayCoordination['amount']) as $digit)
                    <td>{{ $digit }}</td>
                @endforeach
            </tr>
            <tr>
                <td>事業所名称</td>
                <td style="border: 0;"></td>
                <td colspan="18" class="text-left">{{ $statement->copayCoordination['name'] }}</td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border full-width service-type-table">
        <tbody class="text-center">
            <tr>
                <td rowspan="3">サービス<br>種別</td>
                @foreach($statement->daysRecords as $daysRecord)
                    @foreach(str_split($daysRecord['dwsServiceDivisionCode']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>開始年月日</td>
                    <td>{{ $daysRecord['startedOn']['japaneseCalender'] }}</td>
                    @foreach(str_split($daysRecord['startedOn']['year']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>年</td>
                    @foreach(str_split($daysRecord['startedOn']['month']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>月</td>
                    @foreach(str_split($daysRecord['startedOn']['day']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>日</td>
                    <td>終了年月日</td>
                    <td>{{ $daysRecord['terminatedOn']['japaneseCalender'] }}</td>
                    @foreach(str_split($daysRecord['terminatedOn']['year']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>年</td>
                    @foreach(str_split($daysRecord['terminatedOn'] ['month']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>月</td>
                    @foreach(str_split($daysRecord['terminatedOn']['day']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>日</td>
                    <td>利用日数</td>
                    @foreach(str_split($daysRecord['serviceDays']) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td>入院日数</td>
                    <td></td>{{-- 居宅・重訪では利用されないため空欄 --}}
                    <td></td>
                @endforeach
            </tr>
            @for($i = 0; $i < $statement->extraDaysRecordRows(); $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td>開始年月日</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>年</td>
                    <td></td>
                    <td></td>
                    <td>月</td>
                    <td></td>
                    <td></td>
                    <td>日</td>
                    <td>終了年月日</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>年</td>
                    <td></td>
                    <td></td>
                    <td>月</td>
                    <td></td>
                    <td></td>
                    <td>日</td>
                    <td>利用日数</td>
                    <td></td>
                    <td></td>
                    <td>入院日数</td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
    <table class="thick-border full-width details-table">
        <tbody class="text-center">
            <tr class="text-center thick-border-bottom">
                <td rowspan="14" class="thin-width">給付費明細欄</td>
                <td style="width: 24.3%;">サービス内容</td>
                <td colspan="6">サービスコード</td>
                <td colspan="4">単位数</td>
                <td colspan="3">回数</td>
                <td colspan="5">サービス単位数</td>
                <td style="width: 27%;">摘要</td>
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
                <td rowspan="16" class="thin-width">請求額集計欄</td>
                <td colspan="2" style="width: 18.9%;">サービス種類コード</td>
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
                <td colspan="7" rowspan="2">合計</td>
            </tr>
            <tr>
                <td colspan="2">サービス利用日数</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->serviceDays) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td colspan="5" class="text-left">日</td>
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td colspan="5" class="text-left">日</td>
                @endfor
            </tr>
            <tr class="no-colspan">
                <td colspan="2">給付単位数</td>
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
                {{-- 合計 --}}
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
                <td colspan="2">単位数単価</td>
                @if($statement->isLastPage($page))
                    @foreach($statement->aggregates as $aggregate)
                        @foreach(str_split($aggregate->unitCost) as $digit)
                            <td>{{ $digit }}</td>
                        @endforeach
                        <td colspan="3" class="font-x-small">円/単位</td>
                    @endforeach
                @endif
                @for($i = 0; $i < $statement->extraAggregateColumns($page); $i++)
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="3" class="font-x-small">円/単位</td>
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
                <td colspan="2">総費用額</td>
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
                {{-- 合計 --}}
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
                <td colspan="2">1割相当額</td>
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
                <td colspan="2">利用者負担額②</td>
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
                <td colspan="2" class="font-x-small">上限月額調整(①②の内少ない数)</td>
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
                {{-- 合計 --}}
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
                <td rowspan="2">A型減免</td>
                <td>事業者減免額</td>
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
                <td class="font-x-small">減免後利用者負担額</td>
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
                <td colspan="2">調整後利用者負担額</td>
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
                {{-- 合計 --}}
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
                <td colspan="2" class="font-x-small">上限額管理後利用者負担額</td>
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
                {{-- 合計 --}}
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
                <td colspan="2">決定利用者負担額</td>
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
                {{-- 合計 --}}
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
                <td>請求額</td>
                <td>給付費</td>
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
                {{-- 合計 --}}
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
                <td colspan="2">自治体助成分請求額</td>
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
                {{-- 合計 --}}
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
                    <td rowspan="2" style="padding: 0 4px;">特定障害者特別給付費</td>
                    <td colspan="4">算定日額</td>
                    <td colspan="2">日数</td>
                    <td colspan="5">給付費請求額</td>
                    <td colspan="5">実費算定額</td>
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
                        <td>枚中</td>
                        <td>{{ mb_substr(\Domain\Billing\DwsBillingStatementPdf::formatedPage($page), 0, 1) }}</td>
                        <td>{{ mb_substr(\Domain\Billing\DwsBillingStatementPdf::formatedPage($page), 1, 1) }}</td>
                        <td>枚目</td>
                    @else
                        <td></td>
                        <td></td>
                        <td>枚中</td>
                        <td></td>
                        <td></td>
                        <td>枚目</td>
                    @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
