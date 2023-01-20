<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\User\UserDwsSubsidy;
use Infrastructure\User\UserDwsSubsidyAttr;

/**
 * UserDwsSubsidy Fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait UserDwsSubsidyFixture
{
    /**
     * 自治体助成情報 登録.
     */
    protected function createUserDwsSubsidies(): void
    {
        foreach ($this->examples->userDwsSubsidies as $entity) {
            $userDwsSubsidy = UserDwsSubsidy::fromDomain($entity)->saveIfNotExists();
            $userDwsSubsidy->attr()->save(UserDwsSubsidyAttr::fromDomain($entity));
        }
    }
}
