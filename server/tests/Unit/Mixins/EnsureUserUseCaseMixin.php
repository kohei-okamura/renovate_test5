<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\EnsureUserUseCase;

/**
 * {@link \UseCase\User\EnsureUserUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EnsureUserUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\EnsureUserUseCase
     */
    protected $ensureUserUseCase;

    /**
     * EnsureUserUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEnsureUserUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EnsureUserUseCase::class, fn () => $self->ensureUserUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ensureUserUseCase = Mockery::mock(EnsureUserUseCase::class);
        });
    }
}
