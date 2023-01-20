<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\EnsureOfficeUseCase;

/**
 * {@link \UseCase\Office\EnsureOfficeUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EnsureOfficeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\EnsureOfficeUseCase
     */
    protected $ensureOfficeUseCase;

    /**
     * EnsureOfficeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEnsureOfficeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EnsureOfficeUseCase::class, fn () => $self->ensureOfficeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ensureOfficeUseCase = Mockery::mock(EnsureOfficeUseCase::class);
        });
    }
}
