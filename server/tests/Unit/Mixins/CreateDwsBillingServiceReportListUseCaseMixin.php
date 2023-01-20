<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingServiceReportListUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingServiceReportListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingServiceReportListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingServiceReportListUseCase
     */
    protected $createDwsBillingServiceReportListUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingServiceReportListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingServiceReportListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingServiceReportListUseCase::class,
                fn () => $self->createDwsBillingServiceReportListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingServiceReportListUseCase = Mockery::mock(
                CreateDwsBillingServiceReportListUseCase::class
            );
        });
    }
}
