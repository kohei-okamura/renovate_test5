<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingServiceReportRecordListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingServiceReportRecordListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingServiceReportRecordListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingServiceReportRecordListUseCase
     */
    protected $buildDwsBillingServiceReportRecordListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceReportRecordListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingServiceReportRecordListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingServiceReportRecordListUseCase::class,
                fn () => $self->buildDwsBillingServiceReportRecordListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingServiceReportRecordListUseCase = Mockery::mock(
                BuildDwsBillingServiceReportRecordListUseCase::class
            );
        });
    }
}
