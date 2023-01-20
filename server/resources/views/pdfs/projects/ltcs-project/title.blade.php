<div class="title-wrapper">
    <h1 class="title">訪問介護計画書{{ isset($no) ? "（{$no}）": '' }}</h1>
</div>

@once
    @push('css')
        <style>
            .title {
                font-size: 15px;
                height: 10mm;
                text-align: center;
            }
        </style>
    @endpush
@endonce
