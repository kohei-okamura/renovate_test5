<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\DwsAreaGrade\DwsAreaGradeFee;

/**
 * DwsAreaGradeFee Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsAreaGradeFeeFixture
{
    /**
     * 障害福祉サービス：地域区分単価 登録
     *
     * @return void
     */
    protected function createDwsAreaGradeFeeFixture(): void
    {
        foreach ($this->examples->dwsAreaGradeFees as $entity) {
            DwsAreaGradeFee::fromDomain($entity)->save();
        }
    }
}
