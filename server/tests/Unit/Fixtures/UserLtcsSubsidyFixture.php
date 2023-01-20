<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\User\UserLtcsSubsidy;
use Infrastructure\User\UserLtcsSubsidyAttr;

/**
 * UserLtcsSubsidy fixture
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait UserLtcsSubsidyFixture
{
    /**
     * 公費情報 登録.
     *
     * @return void
     */
    protected function createUserLtcsSubsidies(): void
    {
        foreach ($this->examples->userLtcsSubsidies as $entity) {
            $userLtcsSubsidy = UserLtcsSubsidy::fromDomain($entity)->saveIfNotExists();
            $userLtcsSubsidy->attr()->save(UserLtcsSubsidyAttr::fromDomain($entity));
        }
    }
}
