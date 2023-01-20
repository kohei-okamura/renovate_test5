<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\User\UserDwsCalcSpec;
use Infrastructure\User\UserDwsCalcSpecAttr;

/**
 * UserDwsCalcSpec fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait UserDwsCalcSpecFixture
{
    /**
     * 障害福祉サービス：利用者別算定情報 登録.
     *
     * @return void
     */
    protected function createUserDwsCalcSpecs(): void
    {
        foreach ($this->examples->userDwsCalcSpecs as $entity) {
            $userDwsCalcSpec = UserDwsCalcSpec::fromDomain($entity)->saveIfNotExists();
            $userDwsCalcSpec->attr()->save(UserDwsCalcSpecAttr::fromDomain($entity));
        }
    }
}
