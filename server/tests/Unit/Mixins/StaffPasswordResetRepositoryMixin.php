<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Staff\StaffPasswordResetRepository;
use Mockery;

/**
 * StaffPasswordResetRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StaffPasswordResetRepositoryMixin
{
    /**
     * @var \Domain\Staff\StaffPasswordResetRepository|\Mockery\MockInterface
     */
    protected $staffPasswordResetRepository;

    /**
     * StaffPasswordResetRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStaffPasswordResetRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StaffPasswordResetRepository::class, fn () => $self->staffPasswordResetRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->staffPasswordResetRepository = Mockery::mock(StaffPasswordResetRepository::class);
        });
    }
}
