<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsHomeHelpServiceChunk;

/**
 * DwsHomeHelpServiceChunkImpl Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsHomeHelpServiceChunkFixture
{
    /**
     * 障害福祉サービス：サービス単位（居宅介護） 登録.
     */
    protected function createDwsHomeHelpServiceChunk(): void
    {
        foreach ($this->examples->dwsHomeHelpServiceChunks as $entity) {
            $x = DwsHomeHelpServiceChunk::fromDomain($entity);
            $x->save();
        }
    }
}
