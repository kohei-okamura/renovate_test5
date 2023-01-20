<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Staff\StaffRememberTokenRepository;
use Mockery;

/**
 * StaffRememberTokenRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StaffRememberTokenRepositoryMixin
{
    /**
     * @var \Domain\Staff\StaffRememberTokenRepository|\Mockery\MockInterface
     */
    protected $staffRememberTokenRepository;

    /**
     * StaffRememberTokenRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStaffRememberTokenRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StaffRememberTokenRepository::class, fn () => $self->staffRememberTokenRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->staffRememberTokenRepository = Mockery::mock(StaffRememberTokenRepository::class);
        });
    }
}
