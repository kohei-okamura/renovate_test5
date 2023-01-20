<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Staff\StaffPasswordReset;

/**
 * StaffPasswordReset fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait StaffPasswordResetFixture
{
    /**
     * スタッフ：パスワード再設定 登録.
     *
     * @return void
     */
    protected function createStaffPasswordResets(): void
    {
        foreach ($this->examples->staffPasswordResets as $entity) {
            StaffPasswordReset::fromDomain($entity)->saveIfNotExists();
        }
    }
}
