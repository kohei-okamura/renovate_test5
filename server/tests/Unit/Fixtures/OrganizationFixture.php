<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Organization\Organization;
use Infrastructure\Organization\OrganizationAttr;

/**
 * Organization fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait OrganizationFixture
{
    /**
     * 事業者 登録.
     *
     * @return void
     */
    protected function createOrganizations(): void
    {
        foreach ($this->examples->organizations as $entity) {
            $organization = Organization::fromDomain($entity)->saveIfNotExists();
            $organization->attr()->save(OrganizationAttr::fromDomain($entity));
        }
    }
}
