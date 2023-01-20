<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildCopayListPdfParamUseCase;

/**
 * {@link \UseCase\Billing\BuildCopayListPdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildCopayListPdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildCopayListPdfParamUseCase
     */
    protected $buildCopayListPdfParamUseCase;

    /**
     * {@link \UseCase\Billing\BuildCopayListPdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildCopayListPdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildCopayListPdfParamUseCase::class,
                fn () => $self->buildCopayListPdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildCopayListPdfParamUseCase = Mockery::mock(
                BuildCopayListPdfParamUseCase::class
            );
        });
    }
}
