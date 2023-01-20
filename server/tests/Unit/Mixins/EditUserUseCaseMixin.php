<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\EditUserUseCase;

/**
 * EditUserUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditUserUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\EditUserUseCase
     */
    protected $editUserUseCase;

    /**
     * EditUserUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditUserUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditUserUseCase::class, fn () => $self->editUserUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editUserUseCase = Mockery::mock(EditUserUseCase::class);
        });
    }
}
