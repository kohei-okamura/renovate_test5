<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\FindUserUseCase;

/**
 * FindUserUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindUserUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\FindUserUseCase
     */
    protected $findUserUseCase;

    /**
     * FindUserUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindUserUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindUserUseCase::class, fn () => $self->findUserUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findUserUseCase = Mockery::mock(FindUserUseCase::class);
        });
    }
}
