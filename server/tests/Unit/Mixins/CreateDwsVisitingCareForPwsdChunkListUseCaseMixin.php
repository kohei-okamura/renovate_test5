<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsVisitingCareForPwsdChunkListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase
     */
    protected $createDwsVisitingCareForPwsdChunkListUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsVisitingCareForPwsdChunkListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsVisitingCareForPwsdChunkListUseCase::class,
                fn () => $self->createDwsVisitingCareForPwsdChunkListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsVisitingCareForPwsdChunkListUseCase = Mockery::mock(
                CreateDwsVisitingCareForPwsdChunkListUseCase::class
            );
        });
    }
}
