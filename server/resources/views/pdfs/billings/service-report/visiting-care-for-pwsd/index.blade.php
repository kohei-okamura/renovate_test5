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
        <span class="format-block">(様式3-1)</span>
    </div>
    <div class="title-block">
        <span class="date">{{ $pdf->providedIn->formatLocalized('%EC') }}<span class="year">{{ $pdf->providedIn->formatLocalized('%Ey') }}</span>年<span class="month">{{$pdf->providedIn->format('n')}}</span>月分</span>
        <h1 class="title">重度訪問介護サービス提供実績記録票</h1>
    </div>
    <div class="flex full-width font-small">
        <table class="thick-border-top thick-border-bottom thick-border-left text-center user-table" style="width: 65%;">
            <tbody>
                <tr>
                    <td style="width: 15%; padding: 8px 0">受給者証<br>番号</td>
                    @foreach(str_split($pdf->user->dwsNumber) as $digit)
                        <td>{{ $digit }}</td>
                    @endforeach
                    <td style="width: 27%">支給決定障害者氏名</td>
                    <td style="width: 35%">{{ $pdf->user->name->displayName }}</td>
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
                <td rowspan="3" class="font-x-small" style="width: 72px;">サービス提供<br>の状況</td>
                <td colspan="4">重度訪問介護計画</td>
                <td colspan="2" class="font-x-small">サービス提供時間</td>
                <td colspan="2">算定時間数</td>
                <td rowspan="3" style="width: 24px;">派<br>遣<br>人<br>数</td>
                <td rowspan="3" style="width: 24px;">同<br>行<br>支<br>援</td>
                <td rowspan="3" style="width: 48px;">初回<br>加算</td>
                <td rowspan="3" style="width: 48px;">緊急時<br>対応<br>加算</td>
                <td rowspan="3" class="font-x-small" style="width: 48px;">行動障<br>害支援<br>連携<br>加算</td>
                <td rowspan="3" class="font-x-small" style="width: 48px;">移動介<br>護緊急<br>時支援<br>加算</td>
                <td rowspan="3" style="width: 54px;">利用者<br>確認欄</td>
                <td rowspan="3">備考</td>
            </tr>
            <tr>
                <td rowspan="2" style="width: 50px;">開始<br>時間</td>
                <td rowspan="2" style="width: 50px;">終了<br>時間</td>
                <td colspan="2">計画時間数</td>
                <td rowspan="2" style="width: 50px;">開始<br>時間</td>
                <td rowspan="2" style="width: 50px;">終了<br>時間</td>
                <td rowspan="2" style="width: 48px;">時間</td>
                <td rowspan="2" style="width: 48px;">移動</td>
            </tr>
            <tr>
                <td style="width: 48px;">時間</td>
                <td style="width: 48px;">移動</td>
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
                <td colspan="6">移動介護分</td>
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
                <td colspan="6">合計</td>
                <td>{{ $pdf->plan->visitingCareForPwsd }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td>{{ $pdf->result->visitingCareForPwsd }}</td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="no-use"></td>
                <td class="right-justify">{{ $pdf->firstTimeCount }}<span class="font-x-small">回</span></td>
                <td class="right-justify">{{ $pdf->emergencyCount }}<span class="font-x-small">回</span></td>
                <td class="right-justify">{{ $pdf->behavioralDisorderSupportCooperationCount }}<span class="font-x-small">回</span></td>
                <td class="right-justify">{{ $pdf->movingCareSupportCount }}<span class="font-x-small">回</span></td>
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
                    <td>枚中</td>
                    <td>{{ $pdf->currentPageCount }}</td>
                    <td>枚</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
