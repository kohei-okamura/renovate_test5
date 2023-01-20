<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupDwsBillingServiceReportUseCase;

/**
 * {@link \UseCase\Billing\LookupDwsBillingServiceReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsBillingServiceReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupDwsBillingServiceReportUseCase
     */
    protected $lookupDwsBillingServiceReportUseCase;

    /**
     * {@link \UseCase\Billing\LookupDwsBillingServiceReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsBillingServiceReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupDwsBillingServiceReportUseCase::class,
                fn () => $self->lookupDwsBillingServiceReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupDwsBillingServiceReportUseCase = Mockery::mock(
                LookupDwsBillingServiceReportUseCase::class
            );
        });
    }
}
