@push('css')
    <style>
    .main.outer > *:nth-child(1) {
        margin-top: 48px;
        margin-bottom: -30px;
    }
    .main.outer > *:nth-child(n+3) {
        margin-top: 12px;
    }
    .main .font-large {
        font-size: 1.2rem;
    }
    .main .title-block {
        position: relative;
        text-align: center;
    }
    .main .title-block > .date {
        left: 180px;
        position: absolute;
    }
    .main .certification {
        padding: 2px 4px;
        border: solid 1px #000;
    }
    .main .certification > .check {
        padding: 0 4px;
        border-radius: 50%;
        border: solid 1px #000;
    }
    .main .j-calendar > .check, .gender > .check, .day-of-week .check {
        padding: 1px 2px;
        border-radius: 50%;
        border: solid 1px #000;
    }
    .main .j-calendar + div {
        text-align: center;
        font-size: 1rem;
    }
    .main .office-to-office {
        padding: 1px 4px;
        border: solid 1px #000;
        height: 1.3rem;
    }
    .main .year,
    .main .month {
        display: inline-block;
        min-width: 28px;
    }
    .main .title {
        font-size: 1.4rem;
        text-align: center;
    }
    .main .table-fixed {
        table-layout: fixed;
    }
    .main .name-kana {
        border-bottom: 1px solid;
        margin: 0 -1px 0 -1px;
        padding-left: 4px;
    }
    .main .need-care > div {
        align-items: center;
        display: flex;
        height: 20px;
        padding-left: 4px;
    }
    .main .classification > div {
        align-items: center;
        display: flex;
        height: 20px;
        justify-content: center;
    }
    .main .need-care > div:not(:last-child),
    .main .classification > div:not(:last-child) {
        border-bottom: 1px solid;
    }
    .main .float-right {
        float: right;
    }
    .flex.column {
        flex-direction: column;
    }
    .main tr {
        height: 28px;
    }
    .main .user-table td {
        min-width: 22px;
        padding: 0 4px;
    }
    .main .main-table > tbody > tr:nth-child(2n + 1) {
        border-top: solid 2px #000;
    }
    .main .main-table thead tr:nth-child(2),
    .main .main-table thead tr:nth-child(3),
    .main .main-table tbody tr {
        line-height: 1.2rem;
        height: 1.2rem;
    }
    .main .main-table > tbody > tr:nth-child(2n + 1) > td:nth-child(1),
    .main .main-table > tbody > tr:nth-child(2n + 1) > td:nth-child(2),
    .main .main-table > tbody > tr:nth-child(2n + 1) > td:nth-child(3) {
        text-align: left;
        padding-left: 4px;
    }
    .main .page {
        text-align: center
    }
    </style>
@endpush

<div class="main outer flex column">
    <div class="title-block">
        <div class="date">
            {{ $data->providedIn->toEraName() }}
            <span class="year">
                {{ $data->providedIn->toJapaneseYear() }}
            </span>
            年
            <span class="month">
                {{ $data->providedIn->format('n') }}
            </span>
            月分
        </div>
        <h1 class="title">サービス提供票</h1>
    </div>
    <div class="flex justify-between align-end">
        <span class="certification">
            <span @class(['check' => $data->status === \Domain\LtcsInsCard\LtcsInsCardStatus::approved()])>
                認定済
            </span>・
            <span @class(['check' => $data->status === \Domain\LtcsInsCard\LtcsInsCardStatus::applied()])>
               申請中
            </span>
        </span>
        <span class="office-to-office font-x-small">
            <span>サービス事業所</span>→<span>居宅介護支援事業所</span>
        </span>
    </div>
    <table class="full-width thick-border user-table table-fixed" style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="4" style="padding: 0 0 0 4px">保険者<br>番号</td>
                <td colspan="4" style=""></td>
                <td class="text-center">{{ mb_substr($data->insurerNumber, 0, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insurerNumber, 1, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insurerNumber, 2, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insurerNumber, 3, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insurerNumber, 4, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insurerNumber, 5, 1) }}</td>
                <td colspan="6" style="padding-left: 4px;">保険者名</td>
                <td colspan="7" style="padding-left: 4px;">{{ $data->insurerName }}</td>
                <td colspan="6" class="font-x-small" style="padding-left: 4px;">
                    居宅介護支援<br>
                    事業者事業所名
                    <div style="line-height: 0.9rem;">担当者名（TEL）</div>
                </td>
                <td colspan="10" style="padding-left: 4px; line-height: 0.9rem">
                    @if(!empty($data->carePlanAuthorOfficeName)){{ $data->carePlanAuthorOfficeName }}<br>@endif
                    {{ $data->careManagerName }} @if(!empty($data->carePlanAuthorOfficeTel))({{ $data->carePlanAuthorOfficeTel }})@endif
                </td>
                <td colspan="3" style="padding-left: 4px;">
                    作成<br>
                    年月日
                </td>
                <td colspan="7" style="padding-left: 4px;">
                    {{ $data->createdOn }}
                </td>
            </tr>
            <tr>
                <td colspan="4" style="padding: 0 0 0 4px">被保険者<br>番号</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 0, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 1, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 2, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 3, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 4, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 5, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 6, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 7, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 8, 1) }}</td>
                <td class="text-center">{{ mb_substr($data->insNumber, 9, 1) }}</td>
                <td colspan="6" style="padding-left: 4px;">
                    フリガナ<br>
                    被保険者氏名
                </td>
                <td colspan="10" style="padding: 0">
                    <div class="name-kana" style="line-height: 1rem">{{ $data->phoneticDisplayName }}</div>
                    <div class="flex justify-between" style="padding-top: 6px; min-height: 2rem;">
                        <div style="padding-left: 4px;">{{ $data->displayName }}</div>
                        <span style="padding-right: 8px;">様</span>
                    </div>
                </td>
                <td colspan="3">保険者<br>確認印</td>
                <td colspan="10"></td>
                <td colspan="3">届出<br>年月日</td>
                <td colspan="7"></td>
            </tr>
            <tr>
                <td colspan="4" style="padding: 0 0 0 4px">生年月日</td>
                <td colspan="5" style="padding: 0;">
                    <div class="j-calendar text-center">
                        @foreach (['明', '大', '昭', '平'] as $k => $v)
                            @if($k !== 0)
                                <span class="font-x-small" style="letter-spacing: -2px">・</span>
                            @endif
                            <span @class(['check' => mb_substr($data->birthday->toEraName(), 0, 1) === $v])>
                                {{ $v }}
                            </span>
                        @endforeach
                    </div>
                    <div>{{ mb_substr($data->birthday->toJapaneseDate(), 2) }}</div>
                </td>
                <td colspan="2">性別</td>
                <td colspan="3" class="gender text-center">
                    <span @class(['check' => $data->sex === \Domain\Common\Sex::male()])>
                        男
                    </span>・
                    <span @class(['check' => $data->sex === \Domain\Common\Sex::female()])>
                        女
                    </span>
                </td>
                <td colspan="6" class="need-care" style="padding: 0;">
                    <div>要介護状態区分</div>
                    <div>変更後要介護状態区分</div>
                    <div>変更日</div>
                </td>
                <td colspan="7" class="classification" style="padding: 0;">
                    <div>{{ $data->ltcsLevel }}</div>
                    <div>{{ $data->updatedLtcsLevel }}</div>
                    <div>{{ $data->ltcsLevelUpdatedOn }}</div>
                </td>
                <td colspan="3">区分支給<br>限度基準額</td>
                <td colspan="7">
                    <div class="text-center" style="line-height: 1rem;">
                        <div class="float-right" style="line-height: 1.2rem;">単位/月</div>
                        <span class="font-large">{{ $data->maxBenefit }}</span>
                    </div>
                </td>
                <td colspan="3">限度額<br>適用期間</td>
                <td colspan="6" class="text-center">
                    {{ $data->activatedOn }}
                    <div class="font-x-small" style="line-height: 0.7rem">から</div>
                    {{ $data->deactivatedOn }}
                    <div class="font-x-small" style="line-height: 0.7rem">まで</div>
                </td>
                <td colspan="4" class="font-x-small">
                    前月までの短期入所利用日数
                </td>
                <td colspan="3" class="text-right">
                    0日
                </td>
            </tr>
        </tbody>
    </table>
    <table class="thick-border text-center full-width main-table table-fixed">
        <thead>
            <tr>
                <td rowspan="3" colspan="6" class="text-left" style="padding-left: 4px;">提供時間帯</td>
                <td rowspan="3" colspan="6" class="text-left" style="padding-left: 4px;">サービス内容</td>
                <td rowspan="3" colspan="8" class="text-left" style="border-right: none; padding-left: 4px;">
                    サービス<br>
                    事業者<br>
                    事業所名
                </td>
                <td colspan="2"></td>
                <td colspan="35">月間サービス計画及び実績の記録</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="1" style="border-left: solid 1px #000;">日付</td>
                @foreach(range(1, 31) as $day)
                    <td>{{ $day > $data->providedIn->endOfMonth()->day ? '' : $day }}</td>
                @endforeach
                <td rowspan="2" colspan="4">合計<br>回数</td>
            </tr>
            <tr class="day-of-week">
                <td colspan="2" rowspan="1" style="border-left: solid 1px #000;">曜日</td>
                @foreach(range(1, 31) as $day)
                    <td>
                        <span>
                            @if($day <= $data->providedIn->endOfMonth()->day)
                                {{ $data->providedIn->day($day)->isoFormat('ddd') }}
                            @endif
                        </span>
                    </td>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data->entries as $entry)
                <tr>

                    <td rowspan="2" colspan="6">{{ $entry['slot'] }}</td>
                    <td rowspan="2" colspan="6">{{ $entry['serviceName'] }}</td>
                    <td rowspan="2" colspan="8">{{ $entry['officeName'] }}</td>
                    <td colspan="2">予定</td>
                    @foreach(range(1, 31) as $planDay)
                        <td>
                            {{ $entry['plans']->get($planDay)->nonEmpty() ? 1 : '' }}
                        </td>
                    @endforeach
                    <td colspan="4">{{ $entry['plansCount'] }}</td>
                </tr>
                <tr>
                    <td colspan="2">実績</td>
                    @foreach(range(1, 31) as $resultDay)
                        <td>
                            {{ $entry['results']->get($resultDay)->nonEmpty() ? 1 : '' }}
                        </td>
                    @endforeach
                    <td colspan="4">{{ $entry['resultsCount'] }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < 13 - count($data->entries); $i++)
                <tr>
                    <td rowspan="2" colspan="6"></td>
                    <td rowspan="2" colspan="6"></td>
                    <td rowspan="2" colspan="8"></td>
                    <td colspan="2">予定</td>
                    @foreach(range(1, 31) as $x)
                        <td></td>
                    @endforeach
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <td colspan="2">実績</td>
                    @foreach(range(1, 31) as $x)
                        <td></td>
                    @endforeach
                    <td colspan="4"></td>
                </tr>
            @endfor
        </tbody>
    </table>
    @if($data->maxPageCount > 1)
        <div class="page">({{ $data->currentPageCount }}/{{ $data->maxPageCount }})</div>
    @endif
</div>
