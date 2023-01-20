<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingServiceReportListByIdUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase
     */
    protected $buildDwsBillingServiceReportListByIdUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingServiceReportListByIdUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingServiceReportListByIdUseCase::class,
                fn () => $self->buildDwsBillingServiceReportListByIdUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingServiceReportListByIdUseCase = Mockery::mock(
                BuildDwsBillingServiceReportListByIdUseCase::class
            );
        });
    }
}
