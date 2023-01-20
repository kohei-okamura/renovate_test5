<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Staff\StaffRepository;
use Mockery;

/**
 * StaffRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StaffRepositoryMixin
{
    /**
     * @var \Domain\Staff\StaffRepository|\Mockery\MockInterface
     */
    protected $staffRepository;

    /**
     * StaffRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStaffRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StaffRepository::class, fn () => $self->staffRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->staffRepository = Mockery::mock(StaffRepository::class);
        });
    }
}
