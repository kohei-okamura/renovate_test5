<div id="item" class="margin-bottom-3mm">
    <table class="border-bold">
        <thead>
        <tr>
            <th rowspan="3" class="width-25mm text-align-left border-left-bold border-bottom-bold">提供時間帯</th>
            <th rowspan="3" class="width-35mm text-align-left border-left-bold border-bottom-bold">サービス内容</th>
            <th rowspan="3" class="width-40mm text-align-left border-left-bold border-bottom-bold border-right-none">
                <p>サービス</p>
                <p>事業所</p>
                <p>事業所名</p>
            </th>
            <th class="border-bottom-bold border-left-none"></th>
            <th colspan="32" class="font-size-12px border-left-bold border-bottom-bold">月間サービス計画及び実績の記録</th>
        </tr>
        <tr>
            <th class="width-10mm border-left-bold border-right-bold">日付</th>
            @for($i = 1; $i <= 31; $i++)
                <th class="">{{ $i }}</th>
            @endfor
            <th rowspan="2" class="width-15mm border-left-bold border-bottom-bold">合計<br>回数</th>
        </tr>
        <tr>
            <th class="border-left-bold border-right-bold border-bottom-bold">曜日</th>
            @for($i = 1; $i <= 31; $i++)
                <th class="border-bottom-bold"><span class="@if($i % 6 === 1) circle @endif">{{ $i }}</span></th>
            @endfor
        </tr>
        </thead>
        <tbody>
        @for($i = 0; $i < 13; $i++)
            <tr>
                <td rowspan="2" class="border-right-bold border-bottom-bold">10:00-12:00</td>
                <td rowspan="2" class="text-align-left vertical-align-top border-right-bold border-bottom-bold">訪問介護処遇改善加算Ⅰ</td>
                <td rowspan="2" class="text-align-left vertical-align-top border-right-bold border-bottom-bold">土屋訪問介護事業所　世田谷</td>
                <td class="height-4mm">予定</td>
                @for($j = 0; $j < 31; $j++)
                    <td></td>
                @endfor
                <td></td>
            </tr>
            <tr>
                <td class="height-4mm border-bottom-bold">実績</td>
                @for($k = 0; $k < 31; $k++)
                    <td class="border-bottom-bold"></td>
                @endfor
                <td class="border-bottom-bold"></td>
            </tr>
        @endfor
        </tbody>
    </table>
</div>

@once
    @push('css')
        <style>
            #item .circle {
                position: relative;
            }
            #item .circle:before {
                position: absolute;
                content: '';
                top: -1px;
                left: 1px;
                width: 3mm;
                height: 3mm;
                border: 2px solid black;
                border-radius: 60%;
            }
        </style>
    @endpush
@endonce
