@php
    $diagonal1 = base64_encode(app('files')->get(resource_path('images/service-delivery/diagonal-1.png')));
    $diagonal2 = base64_encode(app('files')->get(resource_path('images/service-delivery/diagonal-2.png')));
    $diagonal3 = base64_encode(app('files')->get(resource_path('images/service-delivery/diagonal-3.png')));
    $diagonal4 = base64_encode(app('files')->get(resource_path('images/service-delivery/diagonal-4.png')));
@endphp
<div class="margin-bottom-1mm">
    <table class="border-bold">
        <thead>
        <tr>
            <th rowspan="2" class="width-25mm"><p class="font-size-8px">事業所名</p></th>
            <th rowspan="2" class="width-22mm"><p class="font-size-8px">事業所番号</p></th>
            <th rowspan="2" class="width-25mm"><p class="font-size-8px">サービス内容<br>/種類</p></th>
            <th rowspan="2" class="width-15mm"><p class="font-size-8px">サービス<br>コード</p></th>
            <th rowspan="2" class="width-10mm"><p class="font-size-8px">単位数</p></th>
            <th colspan="2"><p class="font-size-8px">割引適用後</p></th>
            <th rowspan="2" class="width-7mm"><p class="font-size-8px">回数</p></th>
            <th rowspan="2" class="width-15mm"><p class="font-size-8px">サービス<br>単位数<br>/金額</p></th>
            <th rowspan="2" class="width-13mm white-space-nowrap"><p class="font-size-8px">種類支給限度<br>基準を超える<br>単位数</p></th>
            <th rowspan="2" class="width-13mm"><p class="font-size-8px">種類支給限<br>度基準内単<br>位数</p></th>
            <th rowspan="2" class="width-13mm white-space-nowrap"><p class="font-size-8px">区分支給限<br>度基準を超え<br>る単位数</p></th>
            <th rowspan="2" class="width-13mm"><p class="font-size-8px">区分支給限<br>度基準内単<br>位数</p></th>
            <th rowspan="2" class="width-10mm"><p class="font-size-8px">単位数<br>単価</p></th>
            <th rowspan="2" class="width-14mm"><p class="font-size-8px">費用総額<br>(保険/事業<br>対象分)</p></th>
            <th rowspan="2" class="width-7mm"><p class="font-size-8px">給付率<br>(%)</p></th>
            <th rowspan="2"><p class="font-size-8px">保険/事業費<br>請求額</p></th>
            <th rowspan="2" class="white-space-nowrap"><p class="font-size-8px">定額利用者負担<br>単価金額</p></th>
            <th rowspan="2"><p class="font-size-8px">利用者負担<br>(保険/事業<br>対象分)</p></th>
            <th rowspan="2" class="width-14mm"><p class="font-size-8px white-space-nowrap">利用者負担<br>(全額負担分)</p></th>
        </tr>
        <tr>
            <th class="width-7mm"><p class="font-size-8px">率(%)</p></th>
            <th class="width-10mm"><p class="font-size-8px">単位数</p></th>
        </tr>
        </thead>
        <tbody>
        @for($i = 0; $i < 14; $i++)
            <tr>
                @for($j = 0; $j < 20; $j++)
                    <td class="height-6mm @if($j < 4) text-align-left @endif @if($j > 3) text-align-right @endif"> </td>
                @endfor
            </tr>
        @endfor
        <tr>
            <td class="height-6mm"><div class="diagonal-1"></div></td>
            <th colspan="3" class="font-size-14px font-weight-bold">区分支給限度基準額(単位)</th>
            <td colspan="3" class="font-size-14px font-weight-bold text-align-right"></td>
            <th class="font-weight-bold">合計</th>
            <td class="text-align-right">3429</td>
            <td class="text-align-right"></td>
            <td class="text-align-right"></td>
            <td class="text-align-right">0</td>
            <td class="text-align-right">3429</td>
            <td><div class="diagonal-2"></div></td>
            <td class="text-align-right">44448</td>
            <td><div class="diagonal-3"></div></td>
            <td class="text-align-right">40003</td>
            <td><div class="diagonal-4"></div></td>
            <td class="text-align-right">4445</td>
            <td class="text-align-right">0</td>
        </tr>
        </tbody>
    </table>
</div>

@once
    @push('css')
        <style>
            .diagonal-1 {
                width: 25mm;
                height: 6mm;
                background-size: auto;
                background-image: url(data:image/png;base64,{{ $diagonal1 }});
                background-position: right top;
                background-repeat: no-repeat;
            }
            .diagonal-2 {
                width: 10mm;
                height: 6mm;
                background-size: auto;
                background-image: url(data:image/png;base64,{{ $diagonal2 }});
                background-position: right top;
                background-repeat: no-repeat;
            }
            .diagonal-3 {
                width: 7mm;
                height: 6mm;
                background-size: auto;
                background-image: url(data:image/png;base64,{{ $diagonal3 }});
                background-position: right top;
                background-repeat: no-repeat;
            }
            .diagonal-4 {
                width: 14mm;
                height: 6mm;
                background-size: auto;
                background-image: url(data:image/png;base64,{{ $diagonal4 }});
                background-position: right top;
                background-repeat: no-repeat;
            }
        </style>
    @endpush
@endonce
