<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\CreateUserUseCase;

/**
 * CreateUserUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateUserUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\CreateUserUseCase
     */
    protected $createUserUseCase;

    /**
     * CreateUserUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateUserUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateUserUseCase::class, fn () => $self->createUserUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createUserUseCase = Mockery::mock(CreateUserUseCase::class);
        });
    }
}
