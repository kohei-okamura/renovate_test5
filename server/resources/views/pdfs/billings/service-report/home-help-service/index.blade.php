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
        <span class="format-block">(様式1)</span>
    </div>
    <div class="title-block">
        <span class="date">{{ $pdf->providedIn->formatLocalized('%EC') }}<span class="year">{{ $pdf->providedIn->formatLocalized('%Ey') }}</span>年<span class="month">{{$pdf->providedIn->format('n')}}</span>月分</span>
        <h1 class="title">居宅介護サービス提供実績記録票</h1>
    </div>
    <div class="flex full-width font-small">
        <table class="thick-border-top thick-border-bottom thick-border-left text-center user-table" style="width: 65%;">
            <tbody>
                <tr>
                    <td style="width: 15%; padding: 8px 0">受給者証<br>番号</td>
                    @foreach(str_split($pdf->user->dwsNumber) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td style="width: 27%">支給決定障害者氏名<br>（障害児氏名）</td>
                    <td style="width: 35%">
                        {{ $pdf->user->name->displayName }}
                        @if (strlen(trim($pdf->user->childName->displayName)) > 0 )
                            <br>（{{ $pdf->user->childName->displayName }}）
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 0">契約支給量</td>
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
                <span class="flexible">事業所番号</span>
                @foreach(str_split($pdf->office->code) as $digit)
                    <span>{{ $digit }}</span>
                @endforeach
            </div>
            <div class="flex flexible lattice">
                <span class="flex align-center" style="padding: 0 12px;">事業者及び<br>その事業所</span>
                <span class="flexible flex align-center" style="padding: 0 4px;">{{ $pdf->office->name }}</span>
            </div>
        </div>
        <div>
        </div>
    </div>
    <table class="thick-border text-center full-width main-table">
        <thead>
            <tr>
                <td rowspan="3" style="width: 24px;">日<br>付</td>
                <td rowspan="3" style="width: 24px;">曜<br>日</td>
                <td rowspan="3" style="width: 20px;"></td>
                <td rowspan="3" class="font-x-small" style="width: 96px;">サービス<br>内容</td>
                <td colspan="4">居宅介護計画</td>
                <td colspan="4" class="font-x-small">サービス提供時間</td>
                <td colspan="2">算定時間数</td>
                <td rowspan="3" style="width: 24px;">派<br>遣<br>人<br>数</td>
                <td rowspan="3" style="width: 32px;">初<br>回<br>加<br>算</td>
                <td rowspan="3" style="width: 48px;">緊急時<br>対応<br>加算</td>
                <td rowspan="3" class="font-x-small" style="width: 48px;">福祉<br>専門<br>職員等<br>連携</td>
                <td rowspan="3" style="width: 56px;">利用者<br>確認欄</td>
                <td rowspan="3">備考</td>
            </tr>
            <tr>
                <td rowspan="2" style="width: 56px;">開始<br>時間</td>
                <td rowspan="2" style="width: 56px;">終了<br>時間</td>
                <td colspan="2">計画時間数</td>
                <td colspan="2" rowspan="2">開始<br>時間</td>
                <td colspan="2" rowspan="2">終了<br>時間</td>
                <td rowspan="2" style="width: 48px;">時間</td>
                <td rowspan="2" style="width: 48px;">乗降</td>
            </tr>
            <tr>
                <td style="width: 48px;">時間</td>
                <td style="width: 48px;">乗降</td>
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
                    <td></td> {{-- 利用者確認印 --}}
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
                <td colspan="2" rowspan="2">計画<br>時間数計</td>
                <td colspan="4">内訳(適用単価別)</td>
                <td colspan="2" rowspan="2">算定<br>時間数計</td>
                <td rowspan="7" class="no-use"></td>
                <td colspan="3" rowspan="2" class="no-use"></td>
                <td colspan="2" rowspan="7" class="no-use"></td>
            </tr>
            <tr class="font-x-small">
                <td style="width: 28px;">100%</td>
                <td style="width: 28px;">90%</td>
                <td style="width: 28px;">70%</td>
                <td style="width: 28px;">重訪</td>
            </tr>
            <tr>
                <td rowspan="5">合<br>計</td>
                <td colspan="5">居宅における身体介護</td>
                <td>{{ $pdf->plan->physicalCare }}</td>
                <td rowspan="4" class="no-use"></td>
                <td>{{ $pdf->result->physicalCare100 }}</td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->physicalCare70 }}</td>
                <td>{{ $pdf->result->physicalCarePwsd }}</td>
                <td>{{ $pdf->result->physicalCare }}</td>
                <td rowspan="4" class="no-use"></td>
                <td rowspan="5" class="right-justify">{{ $pdf->firstTimeCount }}<span>回</span></td>
                <td rowspan="5" class="right-justify">{{ $pdf->emergencyCount }}<span>回</span></td>
                <td rowspan="5" class="right-justify">{{ $pdf->welfareSpecialistCooperationCount }}<span>回</span></td>
            </tr>
            <tr>
                <td colspan="5">通院介護(身体介護を伴う)</td>
                <td>{{ $pdf->plan->accompanyWithPhysicalCare }}</td>
                <td>{{ $pdf->result->accompanyWithPhysicalCare100 }}</td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->accompanyWithPhysicalCare70 }}</td>
                <td>{{ $pdf->result->accompanyWithPhysicalCarePwsd }}</td>
                <td>{{ $pdf->result->accompanyWithPhysicalCare }}</td>
            </tr>
            <tr>
                <td colspan="5">家事援助</td>
                <td>{{ $pdf->plan->housework }}</td>
                <td>{{ $pdf->result->housework100 }}</td>
                <td>{{ $pdf->result->housework90 }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->housework }}</td>
            </tr>
            <tr>
                <td colspan="5">通院介護(身体介護を伴わない)</td>
                <td>{{ $pdf->plan->accompany }}</td>
                <td>{{ $pdf->result->accompany100 }}</td>
                <td>{{ $pdf->result->accompany90 }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->accompany }}</td>
            </tr>
            <tr>
                <td colspan="5">通院等乗降介助</td>
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
                    <td>枚中</td>
                    <td>{{ $pdf->currentPageCount }}</td>
                    <td>枚</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
