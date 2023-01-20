<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\LtcsAreaGrade\LtcsAreaGrade;

/**
 * LtcsAreaGrade fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsAreaGradeFixture
{
    /**
     * 介護保険地域区分 登録.
     *
     * @return void
     */
    protected function createLtcsAreaGrades(): void
    {
        foreach ($this->examples->ltcsAreaGrades as $entity) {
            LtcsAreaGrade::fromDomain($entity)->saveIfNotExists();
        }
    }
}
