<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\VerifyStaffEmailUseCase;

/**
 * VerifyStaffEmailUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait VerifyStaffEmailUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\VerifyStaffEmailUseCase
     */
    protected $verifyStaffEmailUseCase;

    /**
     * VerifyStaffEmailUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinVerifyStaffEmailUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(VerifyStaffEmailUseCase::class, fn () => $self->verifyStaffEmailUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->verifyStaffEmailUseCase = Mockery::mock(VerifyStaffEmailUseCase::class);
        });
    }
}
