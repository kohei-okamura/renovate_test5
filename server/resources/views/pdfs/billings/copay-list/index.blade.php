<?php
/** @var \Domain\Billing\CopayListPdf[] $copayLists */
?>

@extends('pdfs.base')

@push('css')
    <style>
    html,
    body {
        height: 100%;
        margin: 0;
    }
    .copay-list.outer {
        border: solid 1px #000;
        height: 100%;
        padding: 18px;
        width: 100%;
    }
    .copay-list.outer > *:nth-child(n + 2) {
        margin-top: 16px;
    }
    .copay-list.outer > div:first-of-type {
        margin-bottom: 32px;
    }
    .copay-list .title {
        font-size: 1.3rem;
        text-align: center;
    }
    .copay-list table {
        font-size: 0.9rem;
    }
    .copay-list table.no-border * {
        border: none !important;
    }
    .copay-list td:not(:last-of-type) {
        border-right: solid 1px #000;
    }
    .copay-list td {
        min-width: 22px;
    }
    .copay-list .client-block {
        padding: 24px 42px 24px 0;
    }
    .copay-list .client-name {
        font-size: 1.2rem;
    }
    .copay-list .office-table td {
        padding: 0 6px;
    }
    .copay-list .office-table tr:first-of-type > td:not(:first-of-type) {
        font-size: 00.8rem;
        text-align: center;
    }
    .copay-list .office-table td:first-of-type {
        text-align: center;
    }
    .copay-list .office-table tr:not(:first-of-type) > td:not(:first-of-type) {
        padding: 6px;
    }
    .copay-list .right-block {
        flex: 57% 0 0;
    }
    .copay-list .date-block {
        padding: 10px 36px 22px;
    }
    .copay-list .date-table {
        text-align: center;
    }
    .copay-list .date-table td {
        padding: 2px 6px;
    }
    .copay-list .sir {
        flex: 18px 0 0;
    }
    .copay-list .main-table {
        text-align: center;
    }
    .copay-list .main-table tr {
        height: 30px;
    }
    .copay-list .main-table tr:first-of-type td:first-of-type {
        font-size: 0.6rem;
    }
    .copay-list .main-table tr:not(:first-of-type) {
        font-size: 0.8rem;
    }
    .copay-list .main-table tr:nth-of-type(2) td:nth-last-of-type(1) {
        width: 120px;
    }
    .copay-list .main-table tr:nth-of-type(3n+2) {
        border-top: solid 2px #000;
    }
    .copay-list .main-table tr:nth-of-type(3n+2) td:nth-of-type(n+2):not(:nth-of-type(n+13)) {
        border-bottom: solid 2px #000;
    }
    .copay-list .main-table tr:first-of-type td:first-of-type,
    .copay-list .main-table tr:nth-of-type(3n+2) td:first-of-type {
        border-right: solid 2px #000;
    }
    .copay-list .main-table tr:nth-of-type(3n+2) td:nth-last-of-type(4) {
        border-left: solid 2px #000;
    }
    .copay-list .main-table tr:nth-of-type(3n+2) td:nth-last-of-type(12),
    .copay-list .main-table tr:nth-of-type(3n+3) td:nth-last-of-type(11),
    .copay-list .main-table tr:nth-of-type(3n+4) td:nth-last-of-type(11) {
        border-left: solid 2px #000;
        border-right: solid 2px #000;
    }
    </style>
@endpush

@section('title', '利用者負担額一覧表')

@section('content')
    @foreach($copayLists as $model)
        <section class="sheet copay-list outer flex column">
            <h1 class="title">利用者負担額一覧表</h1>
            <div class="flex justify-between">
                <div class="flex column flexible">
                    <div class="client-block flex flexible column justify-between">
                        <span class="">（ 提 供 先 ）</span>
                        <div class="flex align-baseline">
                            <span class="flexible text-center client-name">{{ $model->copayCoordinationOfficeName }}</span>
                            <span class="sir">様</span>
                        </div>
                        <span style="padding-left: 16px;">下記の通り提供します。</span>
                    </div>
                    <div>
                        <table class="thick-border date-table">
                            <tr>
                                <td>{{ $model->providedIn['japaneseCalender'] }}</td>
                                <td>{{ mb_substr($model->providedIn['year'], 0, 1) }}</td>
                                <td>{{ mb_substr($model->providedIn['year'], 1, 1) }}</td>
                                <td>年</td>
                                <td>{{ mb_substr($model->providedIn['month'], 0, 1) }}</td>
                                <td>{{ mb_substr($model->providedIn['month'], 1, 1) }}</td>
                                <td>月分</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="right-block">
                    <div class="flex justify-end date-block">{{ $model->issuedOn  }}</div>
                    <div>
                        <table class="thick-border office-table full-width">
                            <tr style="height: 30px">
                                <td rowspan="4" style="width: 30px;">
                                    <span>事<br>業<br>者</span>
                                </td>
                                <td class="text-nowrap" style="width: 114px;">指定事業所番号</td>
                                @foreach(str_split($model->officeCode) as $val)
                                    <td>{{ $val }}</td>
                                @endforeach
                            </tr>
                            <tr style="height: 112px">
                                <td>住所<br />（所在地）</td>
                                <td colspan="10" class="font-small" style="vertical-align: baseline;">〒 {{ $model->officeAddr->postcode }}<br>{{ \Domain\Common\Prefecture::resolve($model->officeAddr->prefecture) . $model->officeAddr->city . $model->officeAddr->street . $model->officeAddr->apartment}}</td>
                            </tr>
                            <tr style="height: 30px">
                                <td>電話番号</td>
                                <td colspan="10" class="font-small">{{ $model->officeTel }}</td>
                            </tr>
                            <tr style="height: 90px">
                                <td>名称</td>
                                <td colspan="10" class="font-small">{{ $model->officeName }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <table class="thick-border flexible full-width main-table">
                <tr>
                    <td>項番</td>
                    <td colspan="23">支給決定障害者等欄</td>
                </tr>
                @foreach($model->items as $item)
                    <tr>
                        <td rowspan="3">{{ $item->itemNumber }}</td>
                        <td>市町村番号</td>
                        @foreach(str_split($item->cityCode) as $val)
                            <td>{{ $val }}</td>
                        @endforeach
                        <td class="no-use"></td>
                        <td class="no-use"></td>
                        <td class="no-use"></td>
                        <td class="no-use"></td>
                        <td>総費用額</td>
                        @for($i = 0; $i < (7 - strlen($item->fee)); $i++)
                            <td></td>
                        @endfor
                        @foreach(str_split($item->fee) as $val)
                            <td>{{ $val }}</td>
                        @endforeach
                        <td rowspan="3">提供サービス</td>
                        @empty($item->serviceDivision[0])
                            <td></td>
                            <td></td>
                            <td></td>
                        @else
                            @foreach(str_split($item->serviceDivision[0]->value()) as $val)
                                <td>{{ $val }}</td>
                            @endforeach
                            <td>{{ \Domain\Billing\DwsServiceDivisionCode::resolve($item->serviceDivision[0]) }}</td>
                        @endempty
                    </tr>
                    <tr>
                        <td>受給者証番号</td>
                        @foreach(str_split($item->dwsNumber) as $val)
                            <td>{{ $val }}</td>
                        @endforeach
                        <td>利用者負担額</td>
                        @for($i = 0; $i < (7 - strlen($item->copay)); $i++)
                            <td></td>
                        @endfor
                        @foreach(str_split($item->copay) as $val)
                            <td>{{ $val }}</td>
                        @endforeach
                        @empty($item->serviceDivision[1])
                            <td></td>
                            <td></td>
                            <td></td>
                        @else
                            @foreach(str_split($item->serviceDivision[1]->value()) as $val)
                                <td>{{ $val }}</td>
                            @endforeach
                            <td>{{ \Domain\Billing\DwsServiceDivisionCode::resolve($item->serviceDivision[1]) }}</td>
                        @endempty
                    </tr>
                    <tr>
                        <td>氏名</td>
                        <td colspan="10">{{ $item->name }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        @empty($item->serviceDivision[2])
                            <td></td>
                            <td></td>
                            <td></td>
                        @else
                            @foreach(str_split($item->serviceDivision[2]->value()) as $val)
                                <td>{{ $val }}</td>
                            @endforeach
                            <td>{{ \Domain\Billing\DwsServiceDivisionCode::resolve($item->serviceDivision[2]) }}</td>
                        @endempty
                    </tr>
                @endforeach
                @for($i = 0; $i < (10 - count($model->items)); $i++)
                    <tr>
                        <td rowspan="3"></td>
                        <td>市町村番号</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="no-use"></td>
                        <td class="no-use"></td>
                        <td class="no-use"></td>
                        <td class="no-use"></td>
                        <td>総費用額</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td rowspan="3">提供サービス</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>受給者証番号</td>
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
                        <td>利用者負担額</td>
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
                    <tr>
                        <td>氏名</td>
                        <td colspan="10"></td>
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
            </table>
        </section>
    @endforeach
@endsection
