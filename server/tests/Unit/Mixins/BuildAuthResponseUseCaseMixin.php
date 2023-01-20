<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\BuildAuthResponseUseCase;

/**
 * {@link \UseCase\Staff\BuildAuthResponseUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildAuthResponseUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\BuildAuthResponseUseCase
     */
    protected $buildAuthResponseUseCase;

    /**
     * BuildAuthResponseUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildAuthResponseUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                BuildAuthResponseUseCase::class,
                fn () => $self->buildAuthResponseUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->buildAuthResponseUseCase = Mockery::mock(
                BuildAuthResponseUseCase::class
            );
        });
    }
}
