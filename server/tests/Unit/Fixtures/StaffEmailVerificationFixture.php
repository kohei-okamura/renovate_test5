<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Staff\StaffEmailVerification;

/**
 * StaffEmailVerification fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait StaffEmailVerificationFixture
{
    /**
     * スタッフ：メールアドレス確認 登録.
     *
     * @return void
     */
    protected function createStaffEmailVerifications(): void
    {
        foreach ($this->examples->staffEmailVerifications as $entity) {
            StaffEmailVerification::fromDomain($entity)->saveIfNotExists();
        }
    }
}
