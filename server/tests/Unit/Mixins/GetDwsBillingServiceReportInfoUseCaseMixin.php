<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetDwsBillingServiceReportInfoUseCase;

/**
 * {@link \UseCase\Billing\GetDwsBillingServiceReportInfoUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetDwsBillingServiceReportInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetDwsBillingServiceReportInfoUseCase
     */
    protected $getDwsBillingServiceReportInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetDwsBillingServiceReportInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetDwsBillingServiceReportInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetDwsBillingServiceReportInfoUseCase::class,
                fn () => $self->getDwsBillingServiceReportInfoUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getDwsBillingServiceReportInfoUseCase = Mockery::mock(
                GetDwsBillingServiceReportInfoUseCase::class
            );
        });
    }
}
