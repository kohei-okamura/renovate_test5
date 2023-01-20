<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Staff\StaffRememberToken;

/**
 * StaffRememberToken fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait StaffRememberTokenFixture
{
    /**
     * スタフ：リメンバートークン 登録.
     *
     * @return void
     */
    protected function createStaffRememberTokens(): void
    {
        foreach ($this->examples->staffRememberTokens as $entity) {
            StaffRememberToken::fromDomain($entity)->saveIfNotExists();
        }
    }
}
