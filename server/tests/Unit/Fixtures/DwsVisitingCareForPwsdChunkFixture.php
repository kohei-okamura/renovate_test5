<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsVisitingCareForPwsdChunk;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunk} Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsVisitingCareForPwsdChunkFixture
{
    /**
     * 障害福祉サービス サービス単位（重度訪問介護） の登録.
     */
    protected function createDwsVisitingCareForPwsdChunk(): void
    {
        foreach ($this->examples->dwsVisitingCareForPwsdChunks as $entity) {
            DwsVisitingCareForPwsdChunk::fromDomain($entity)->save();
        }
    }
}
