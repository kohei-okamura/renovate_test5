<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase;

/**
 * {@link \UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SimpleLookupDwsBillingServiceReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase
     */
    protected $simpleLookupDwsBillingServiceReportUseCase;

    /**
     * {@link \UseCase\Billing\SimpleLookupDwsBillingServiceReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSimpleLookupDwsBillingServiceReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                SimpleLookupDwsBillingServiceReportUseCase::class,
                fn () => $self->simpleLookupDwsBillingServiceReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->simpleLookupDwsBillingServiceReportUseCase = Mockery::mock(
                SimpleLookupDwsBillingServiceReportUseCase::class
            );
        });
    }
}
