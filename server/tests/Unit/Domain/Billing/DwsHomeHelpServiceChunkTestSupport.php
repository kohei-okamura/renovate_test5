<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）関連テストのためのユーティリティ.
 */
trait DwsHomeHelpServiceChunkTestSupport
{
    protected DwsHomeHelpServiceChunk $baseChunk;
    protected DwsHomeHelpServiceFragment $baseFragment;

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
        $this->baseFragment = DwsHomeHelpServiceFragment::create([
            'providerType' => DwsHomeHelpServiceProviderType::none(),
            'isSecondary' => false,
            'range' => $range,
            'headcount' => 1,
        ]);
        $this->baseChunk = DwsHomeHelpServiceChunkImpl::create([
            'userId' => 1,
            'category' => DwsServiceCodeCategory::physicalCare(),
            'buildingType' => DwsHomeHelpServiceBuildingType::none(),
            'isEmergency' => false,
            'isFirst' => false,
            'isWelfareSpecialistCooperation' => false,
            'isPlannedByNovice' => false,
            'range' => $range,
            'fragments' => Seq::from($this->baseFragment),
        ]);
    }

    /**
     * 指定した属性を持つ要素を生成する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param array $attrs
     * @return \Domain\Billing\DwsHomeHelpServiceFragment
     */
    protected function makeFragment(Carbon $start, Carbon $end, array $attrs = []): DwsHomeHelpServiceFragment
    {
        $range = CarbonRange::create(compact('start', 'end'));
        return $this->baseFragment->copy(compact('range') + $attrs);
    }

    /**
     * 指定した要素を持つサービス単位を生成する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param array $chunkAttrs
     * @param \Domain\Billing\DwsHomeHelpServiceFragment ...$fragments
     * @return array|\Domain\Billing\DwsHomeHelpServiceChunk[]
     */
    protected function makeChunkWithFragments(
        Carbon $start,
        Carbon $end,
        array $chunkAttrs,
        DwsHomeHelpServiceFragment ...$fragments
    ): array {
        $chunk = $this->baseChunk
            ->copy($chunkAttrs)
            ->copy([
                'providedOn' => $start->startOfDay(),
                'range' => CarbonRange::create(compact('start', 'end')),
                'fragments' => Seq::from(...$fragments),
            ]);
        return [$chunk];
    }

    /**
     * 指定した要素を持つサービス単位を生成する.
     *
     * @param \Carbon\CarbonImmutable|\Domain\Common\Carbon $start
     * @param \Carbon\CarbonImmutable|\Domain\Common\Carbon $end
     * @param array $chunkAttrs
     * @param array $fragmentAttrs
     * @return array|\Domain\Billing\DwsHomeHelpServiceChunk[]
     */
    protected function makeChunkWithRange(
        Carbon $start,
        Carbon $end,
        array $chunkAttrs = [],
        array $fragmentAttrs = []
    ): array {
        $fragment = $this->makeFragment($start, $end, $fragmentAttrs);
        return $this->makeChunkWithFragments($start, $end, $chunkAttrs, $fragment);
    }
}
