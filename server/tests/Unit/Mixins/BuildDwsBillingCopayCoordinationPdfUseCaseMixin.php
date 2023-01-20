<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingCopayCoordinationPdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamUseCase
     */
    protected $buildDwsBillingCopayCoordinationPdfUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationPdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingCopayCoordinationPdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingCopayCoordinationPdfParamUseCase::class,
                fn () => $self->buildDwsBillingCopayCoordinationPdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingCopayCoordinationPdfUseCase = Mockery::mock(
                BuildDwsBillingCopayCoordinationPdfParamUseCase::class
            );
        });
    }
}
