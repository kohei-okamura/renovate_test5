<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\GetStaffEmailVerificationUseCase;

/**
 * GetStaffPasswordResetUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetStaffEmailVerificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\GetStaffEmailVerificationUseCase
     */
    protected $getStaffEmailVerificationUseCase;

    /**
     * GetStaffEmailVerificationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinTokenMaker(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetStaffEmailVerificationUseCase::class, fn () => $self->getStaffEmailVerificationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getStaffEmailVerificationUseCase = Mockery::mock(GetStaffEmailVerificationUseCase::class);
        });
    }
}
