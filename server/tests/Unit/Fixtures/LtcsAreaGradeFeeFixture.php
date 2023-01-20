<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\LtcsAreaGrade\LtcsAreaGradeFee;

/**
 * LtcsAreaGradeFee Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsAreaGradeFeeFixture
{
    /**
     * 介護保険サービス：地域区分単価 登録.
     *
     * @return void
     */
    protected function createLtcsAreaGradeFeeFixture(): void
    {
        foreach ($this->examples->ltcsAreaGradeFees as $entity) {
            LtcsAreaGradeFee::fromDomain($entity)->save();
        }
    }
}
