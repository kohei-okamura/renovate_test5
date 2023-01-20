<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\CreateStaffPasswordResetUseCase;

/**
 * CreateStaffPasswordResetUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateStaffPasswordResetUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\CreateStaffPasswordResetUseCase
     */
    protected $createStaffPasswordResetUseCase;

    /**
     * CreateStaffPasswordResetUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateStaffPasswordResetUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateStaffPasswordResetUseCase::class, fn () => $self->createStaffPasswordResetUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createStaffPasswordResetUseCase = Mockery::mock(CreateStaffPasswordResetUseCase::class);
        });
    }
}
