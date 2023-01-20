<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\GetStaffPasswordResetUseCase;

/**
 * GetStaffPasswordResetUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetStaffPasswordResetUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\GetStaffPasswordResetUseCase
     */
    protected $getStaffPasswordResetUseCase;

    /**
     * GetStaffPasswordResetUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinTokenMaker(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetStaffPasswordResetUseCase::class, fn () => $self->getStaffPasswordResetUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getStaffPasswordResetUseCase = Mockery::mock(GetStaffPasswordResetUseCase::class);
        });
    }
}
