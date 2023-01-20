<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\GetUserInfoUseCase;

/**
 * GetUserInfoUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetUserInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\GetUserInfoUseCase
     */
    protected $getUserInfoUseCase;

    /**
     * GetUserInfoUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetUserInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetUserInfoUseCase::class, fn () => $self->getUserInfoUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getUserInfoUseCase = Mockery::mock(GetUserInfoUseCase::class);
        });
    }
}
