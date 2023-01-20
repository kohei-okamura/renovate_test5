<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsVisitingCareForPwsdServiceDetailListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListUseCase
     */
    protected $buildDwsVisitingCareForPwsdServiceDetailListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsVisitingCareForPwsdServiceDetailListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsVisitingCareForPwsdServiceDetailListUseCase::class,
                fn () => $self->buildDwsVisitingCareForPwsdServiceDetailListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsVisitingCareForPwsdServiceDetailListUseCase = Mockery::mock(
                BuildDwsVisitingCareForPwsdServiceDetailListUseCase::class
            );
        });
    }
}
