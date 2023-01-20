<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\GetIndexOfficeUseCase;

/**
 * {@link \UseCase\Office\GetIndexOfficeUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetIndexOfficeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\GetIndexOfficeUseCase
     */
    protected $getIndexOfficeUseCase;

    /**
     * {@link \UseCase\Office\GetIndexOfficeUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetIndexOfficeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetIndexOfficeUseCase::class,
                fn () => $self->getIndexOfficeUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getIndexOfficeUseCase = Mockery::mock(
                GetIndexOfficeUseCase::class
            );
        });
    }
}
