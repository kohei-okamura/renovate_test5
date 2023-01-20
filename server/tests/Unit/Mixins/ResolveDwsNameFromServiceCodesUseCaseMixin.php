<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ResolveDwsNameFromServiceCodesUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase
     */
    protected $resolveDwsNameFromServiceCodesUseCase;

    /**
     * {@link \UseCase\ServiceCodeDictionary\ResolveDwsNameFromServiceCodesUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinResolveDwsNameFromServiceCodesUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ResolveDwsNameFromServiceCodesUseCase::class,
                fn () => $self->resolveDwsNameFromServiceCodesUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->resolveDwsNameFromServiceCodesUseCase = Mockery::mock(
                ResolveDwsNameFromServiceCodesUseCase::class
            );
        });
    }
}
