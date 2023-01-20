<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Office\OfficeGroup;

/**
 * OfficeGroup fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait OfficeGroupFixture
{
    /**
     * 事業所グループ 登録.
     *
     * @return void
     */
    protected function createOfficeGroups(): void
    {
        foreach ($this->examples->officeGroups as $entity) {
            OfficeGroup::fromDomain($entity)->saveIfNotExists();
        }
    }
}
