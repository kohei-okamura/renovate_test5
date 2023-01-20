@php
    $diagonal5 = base64_encode(app('files')->get(resource_path('images/service-delivery/diagonal-5.png')));
@endphp
<div>
    <table class="border-none">
        <tr>
            <td class="width-60p border-none">
                <div class="margin-left-5mm">
                    <div class="margin-bottom-1mm">
                        <p class="text-align-left">種類別支給限度管理</p>
                        <table class="width-auto border-bold">
                            <thead>
                            <tr>
                                <th class="height-6mm width-15mm"><div class="font-size-8px">サービス種類</div></th>
                                <th class="width-20mm"><div class="font-size-8px">種類支給限度<br>基準額(単位)</div></th>
                                <th class="width-15mm"><div class="font-size-8px">合計単位数</div></th>
                                <th class="width-20mm border-right-bold"><div class="font-size-8px">種類支給限度基準<br>を超える単位数</div></th>
                                <th class="width-15mm"><div class="font-size-8px">サービス種類</div></th>
                                <th class="width-20mm"><div class="font-size-8px">種類支給限度<br>基準額(単位)</div></th>
                                <th class="width-15mm"><div class="font-size-8px">合計単位数</div></th>
                                <th><div class="font-size-8px width-20mm">種類支給限度基準<br>を超える単位数</div></th>
                            </tr>
                            </thead>
                            <tbody>
                            @for($i = 0; $i < 6; $i++)
                                <tr>
                                    @for($j = 0; $j < 8; $j++)
                                        <td class="height-6mm text-align-right @if($j === 3) border-right-bold @endif"></td>
                                    @endfor
                                </tr>
                            @endfor
                            <tr>
                                <td class="height-6mm"></td>
                                <td></td>
                                <td></td>
                                <td class="border-right-bold"></td>
                                <th class="font-size-14px text-align-left border-top-bold">合計</th>
                                <td colspan="2" class="border-top-bold"><div class="diagonal-5"></div></td>
                                <td class="font-size-14px text-align-right border-top-bold">0</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <p class="text-align-left">要介護認定期間中の短期入所利用日数</p>
                        <table class="width-auto border-bold">
                            <tbody>
                            <tr>
                                <th class="height-6mm width-20mm"><div class="font-size-8px">前月までの利用日数</div></th>
                                <th class="width-20mm"><div class="font-size-8px">当月の計画利用日数</div></th>
                                <th class="width-20mm"><div class="font-size-8px">累積利用日数</div></th>
                            </tr>
                            <tr>
                                <td class="font-size-14px text-align-right height-6mm">0</td>
                                <td class="font-size-14px text-align-right">0</td>
                                <td class="font-size-14px text-align-right">0</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
            <td class="text-align-right vertical-align-top border-none">
                <p class="font-size-14px font-weight-bold margin-top-50mm text-align-left">請求額の計算</p>
                <table class="border-bold">
                        <tbody>
                        <tr>
                            <th class="height-6mm width-20mm"><div class="font-size-8px">保険請求分</div></th>
                            <th class="width-30mm"><div class="font-size-8px">公費請求額</div></th>
                            <th class="width-20mm"><div class="font-size-8px">社会福祉法人等によ<br>る利用者負担の減免</div></th>
                            <th class="width-30mm"><div class="font-size-8px">利用者請求額</div></th>
                        </tr>
                        <tr>
                            <td class="font-size-14px text-align-right height-6mm">40003</td>
                            <td class="font-size-14px text-align-right">0</td>
                            <td class="font-size-14px text-align-right">0</td>
                            <td class="font-size-14px text-align-right">4445</td>
                        </tr>
                        </tbody>
                    </table>
            </td>
        </tr>
    </table>
</div>

@once
    @push('css')
        <style>
            .diagonal-5 {
                width: 35mm;
                height: 6mm;
                background-size: auto;
                background-image: url(data:image/png;base64,{{ $diagonal5 }});
                background-position: right top;
                background-repeat: no-repeat;
            }
        </style>
    @endpush
@endonce
