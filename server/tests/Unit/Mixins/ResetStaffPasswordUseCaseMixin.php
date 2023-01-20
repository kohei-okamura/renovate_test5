<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\ResetStaffPasswordUseCase;

/**
 * ResetStaffPasswordUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ResetStaffPasswordUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\ResetStaffPasswordUseCase
     */
    protected $resetStaffPasswordUseCase;

    /**
     * ResetStaffPasswordUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinResetStaffPasswordUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ResetStaffPasswordUseCase::class, fn () => $self->resetStaffPasswordUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->resetStaffPasswordUseCase = Mockery::mock(ResetStaffPasswordUseCase::class);
        });
    }
}
