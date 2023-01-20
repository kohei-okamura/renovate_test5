<div id="header" class="height-12mm margin-top-15mm border-white">
    <div class="float-left padding-2mm margin-top-2mm margin-right-7mm width-25mm text-align-center border">
        <span class="circle margin-right-2mm">認定済</span>
        <span class="margin-right-2mm">・</span>
        <span>申請中</span>
    </div>
    <p class="float-left margin-right-50mm font-size-16px">令和2年3月 分</p>
    <p class="float-left font-size-18px font-weight-bold">サービス提供票</p>
    <p class="block-right margin-top-7mm width-55mm border text-align-center vertical-align-middle">サービス事業所→居宅介護支援事業所</p>
</div>

@once
    @push('css')
        <style>
            #header .circle {
                position: relative;
            }
            #header .circle:before {
                position: absolute;
                content: '';
                top: -1mm;
                left: -1mm;
                width: 10mm;
                height: 4mm;
                border: 2px solid black;
                border-radius: 80%;
            }
        </style>
    @endpush
@endonce
