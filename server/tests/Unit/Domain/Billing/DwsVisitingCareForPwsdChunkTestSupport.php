<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護）関連テストのためのユーティリティ.
 */
trait DwsVisitingCareForPwsdChunkTestSupport
{
    protected DwsVisitingCareForPwsdChunk $baseChunk;
    protected DwsVisitingCareForPwsdFragment $baseFragment;

    /**
     * テスト用のデータを生成してプロパティに設定する.
     *
     * @return void
     */
    protected function setupTestData(): void
    {
        $range = CarbonRange::create([
            'start' => Carbon::create(2021, 2, 11, 14, 0),
            'end' => Carbon::create(2021, 2, 11, 22, 0),
        ]);
        $this->baseFragment = DwsVisitingCareForPwsdFragment::create([
            'isHospitalized' => false,
            'isLongHospitalized' => false,
            'isCoaching' => false,
            'isMoving' => false,
            'isSecondary' => false,
            'movingDurationMinutes' => 0,
            'range' => $range,
            'headcount' => 1,
        ]);
        $this->baseChunk = DwsVisitingCareForPwsdChunkImpl::create([
            'userId' => 1,
            'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
            'isEmergency' => false,
            'providedOn' => Carbon::now()->startOfDay(),
            'range' => $range,
            'isFirst' => false,
            'isBehavioralDisorderSupportCooperation' => false,
            'fragments' => Seq::from($this->baseFragment),
        ]);
    }

    /**
     * 指定した属性を持つ要素を生成する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param array $attrs
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment
     */
    protected function makeFragment(Carbon $start, Carbon $end, array $attrs = []): DwsVisitingCareForPwsdFragment
    {
        $range = CarbonRange::create(compact('start', 'end'));
        return $this->baseFragment->copy(compact('range') + $attrs);
    }

    /**
     * 指定した要素を持つサービス単位を生成する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment ...$fragments
     * @return array|\Domain\Billing\DwsVisitingCareForPwsdChunk[]
     */
    protected function makeChunkWithFragments(
        Carbon $start,
        Carbon $end,
        DwsVisitingCareForPwsdFragment ...$fragments
    ): array {
        $chunk = $this->baseChunk->copy([
            'providedOn' => $start->startOfDay(),
            'range' => CarbonRange::create(compact('start', 'end')),
            'fragments' => Seq::from(...$fragments),
        ]);
        return [$chunk];
    }

    /**
     * 指定した要素を持つサービス単位を生成する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param array $fragmentAttrs
     * @return array|\Domain\Billing\DwsVisitingCareForPwsdChunk[]
     */
    protected function makeChunkWithRange(Carbon $start, Carbon $end, array $fragmentAttrs = []): array
    {
        $fragment = $this->makeFragment($start, $end, $fragmentAttrs);
        return $this->makeChunkWithFragments($start, $end, $fragment);
    }
}
