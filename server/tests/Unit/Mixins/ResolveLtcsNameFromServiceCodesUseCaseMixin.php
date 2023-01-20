<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ResolveLtcsNameFromServiceCodesUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase
     */
    protected $resolveLtcsNameFromServiceCodesUseCase;

    /**
     * {@link \UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinResolveLtcsNameFromServiceCodesUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ResolveLtcsNameFromServiceCodesUseCase::class,
                fn () => $self->resolveLtcsNameFromServiceCodesUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->resolveLtcsNameFromServiceCodesUseCase = Mockery::mock(
                ResolveLtcsNameFromServiceCodesUseCase::class
            );
        });
    }
}
