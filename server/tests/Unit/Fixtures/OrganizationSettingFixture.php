<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Organization\OrganizationSetting;

/**
 * OrganizationSetting fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait OrganizationSettingFixture
{
    /**
     * 事業者別設定 登録.
     *
     * @return void
     */
    protected function createOrganizationSettings(): void
    {
        foreach ($this->examples->organizationSettings as $entity) {
            OrganizationSetting::fromDomain($entity)->saveIfNotExists();
        }
    }
}
