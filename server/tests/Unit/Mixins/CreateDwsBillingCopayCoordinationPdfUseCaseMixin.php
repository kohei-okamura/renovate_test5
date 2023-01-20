<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingCopayCoordinationPdfUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationPdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingCopayCoordinationPdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingCopayCoordinationPdfUseCase
     */
    protected $createDwsBillingCopayCoordinationPdfUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationPdfUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingCopayCoordinationPdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingCopayCoordinationPdfUseCase::class,
                fn () => $self->createDwsBillingCopayCoordinationPdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingCopayCoordinationPdfUseCase = Mockery::mock(
                CreateDwsBillingCopayCoordinationPdfUseCase::class
            );
        });
    }
}
