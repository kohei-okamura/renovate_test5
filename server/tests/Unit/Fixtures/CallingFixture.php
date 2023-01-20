<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Calling\Calling;

/**
 * Calling fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait CallingFixture
{
    /**
     * 出勤確認 登録.
     *
     * @return void
     */
    protected function createCallings(): void
    {
        foreach ($this->examples->callings as $entity) {
            $calling = Calling::fromDomain($entity)->saveIfNotExists();
            $calling->shifts()->sync($entity->shiftIds);
        }
    }
}
