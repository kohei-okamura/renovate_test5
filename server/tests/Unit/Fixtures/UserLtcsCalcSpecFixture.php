<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\User\UserLtcsCalcSpec;
use Infrastructure\User\UserLtcsCalcSpecAttr;

/**
 * UserLtcsCalcSpec fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait UserLtcsCalcSpecFixture
{
    /**
     * 介護保険サービス：利用者別算定情報 登録.
     *
     * @return void
     */
    protected function createUserLtcsCalcSpecs(): void
    {
        foreach ($this->examples->userLtcsCalcSpecs as $entity) {
            $userLtcsCalcSpec = UserLtcsCalcSpec::fromDomain($entity)->saveIfNotExists();
            $userLtcsCalcSpec->attr()->save(UserLtcsCalcSpecAttr::fromDomain($entity));
        }
    }
}
