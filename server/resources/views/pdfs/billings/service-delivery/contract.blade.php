<div id="contract" class="margin-bottom-3mm">
    <table class="border-bold">
        <tbody>
        <tr>
            <th class="height-10mm width-18mm text-align-left">保険者<br>番号</th>
            <td colspan="4"></td>
            @for($i = 0; $i < 6; $i++)
                <td class="font-size-16px font-weight-bold width-5mm">0</td>
            @endfor
            <th class="width-30mm text-align-left">保険者名</th>
            <td class="width-40mm text-align-left">世田谷区</td>
            <th class="width-23mm text-align-left">
                <p>居宅介護支援</p>
                <p>事業者事業所名</p>
                <p>担当者名(TEL)</p>
            </th>
            <td colspan="4" class="width-63mm text-align-left">
                <p class="heigh-3mm">あけぼの介護センター経堂</p>
                <p class="heigh-3mm">&nbsp;</p>
                <p class="heigh-3mm">木村 美保子(000-0000-0000)</p>
            </td>
            <th class="width-15mm text-align-left">作成<br>年月日</th>
            <td colspan="2" class="font-size-16px font-weight-bold text-align-right">令和2年12月16日</td>
        </tr>
        <tr>
            <th class="height-10mm text-align-left">被保険者<br>番号</th>
            @for($i = 0; $i < 10; $i++)
                <td class="font-size-16px font-weight-bold width-5mm">0</td>
            @endfor
            <th class="text-align-left vertical-align-middle">
                <p class="margin-bottom-1mm vertical-align-middle">フリガナ</p>
                <p class="height-6mm vertical-align-middle">被保険者氏名</p>
            </th>
            <td colspan="2">
                <p class="margin-bottom-1mm text-align-left vertical-align-middle border-bottom">シノムラ ルリコ</p> {{-- フリガナ --}}
                <p class="height-6mm text-align-left vertical-align-middle">篠村 るり子 様</p>
            </td>
            <th class="width-15mm text-align-left">保険者<br>確認印</th>
            <td colspan="3"></td>
            <th class="text-align-left">届出<br>年月日</th>
            <td colspan="2"></td>
        </tr>
        <tr>
            <th class="height-12mm text-align-left">生年月日</th>
            <td colspan="5">
                <p class="margin-bottom-3mm"><span>明</span> ・ <span>大</span> ・ <span class="circle">昭</span> ・ <span>平</span></p>
                <p>27年11月30日</p>
            </td>
            <th colspan="2">性別</th>
            <td colspan="3"><p><span class="circle">男</span> ・ <span>女</span></p></td>
            <th class="vertical-align-top">
                <p class="height-4mm text-align-left border-bottom">要介護状態区分</p>
            </th>
            <td>
                <p class="height-4mm text-align-center border-bottom">要介護1</p> {{-- 要介護区分 --}}
                <p class="height-5mm border-bottom">&nbsp;</p>
                <p class="height-5mm">&nbsp;</p>
            </td>
            <th class="text-align-left">区分支給<br>限度基準額</th>
            <td colspan="2">
                <p class="font-size-16px font-weight-bold">16765</p>
                <p class="text-align-right vertical-align-bottom">単位/月</p>
            </td>
            <th class="text-align-left">限度額<br>適用期間</th>
            <td colspan="2">
                <p class="font-size-12px">平成30年4月</p>
                <span class="font-size-10px">から</span>
                <p class="font-size-12px">令和2年3月</p>
                <span class="font-size-10px">まで</span>
            </td>
            <th class="text-align-left">前月まで<br>の短期入所<br>利用日数</th>
            <td class="text-align-right"><span class="font-size-16px font-weight-bold">0</span>日</td>
        </tr>
        </tbody>
    </table>
</div>

@once
    @push('css')
        <style>
            #contract .circle {
                position: relative;
            }
            #contract .circle:before {
                position: absolute;
                content: '';
                top: -1mm;
                left: 2mm;
                width: 4mm;
                height: 4mm;
                border: 2px solid black;
                border-radius: 80%;
            }
        </style>
    @endpush
@endonce
