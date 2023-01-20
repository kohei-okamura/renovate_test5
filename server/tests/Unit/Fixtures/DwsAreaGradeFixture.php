<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\DwsAreaGrade\DwsAreaGrade;

/**
 * DwsAreaGrade fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsAreaGradeFixture
{
    /**
     * 障害福祉サービス:地域区分 登録.
     *
     * @return void
     */
    protected function createDwsAreaGrades(): void
    {
        foreach ($this->examples->dwsAreaGrades as $entity) {
            DwsAreaGrade::fromDomain($entity)->saveIfNotExists();
        }
    }
}
