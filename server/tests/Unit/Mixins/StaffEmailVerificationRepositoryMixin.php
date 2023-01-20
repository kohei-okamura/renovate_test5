<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Staff\StaffEmailVerificationRepository;
use Mockery;

/**
 * StaffEmailVerificationRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StaffEmailVerificationRepositoryMixin
{
    /**
     * @var \Domain\Staff\StaffEmailVerificationRepository|\Mockery\MockInterface
     */
    protected $staffEmailVerificationRepository;

    /**
     * StaffEmailVerificationRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStaffEmailVerificationRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StaffEmailVerificationRepository::class, fn () => $self->staffEmailVerificationRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->staffEmailVerificationRepository = Mockery::mock(StaffEmailVerificationRepository::class);
        });
    }
}
