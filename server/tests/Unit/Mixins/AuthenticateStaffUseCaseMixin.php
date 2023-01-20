<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\AuthenticateStaffUseCase;

/**
 * AuthenticateStaffUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait AuthenticateStaffUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\AuthenticateStaffUseCase
     */
    protected $authenticateStaffUseCase;

    /**
     * AuthenticateStaffUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinAuthenticateStaffUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(AuthenticateStaffUseCase::class, fn () => $self->authenticateStaffUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->authenticateStaffUseCase = Mockery::mock(AuthenticateStaffUseCase::class);
        });
    }
}
