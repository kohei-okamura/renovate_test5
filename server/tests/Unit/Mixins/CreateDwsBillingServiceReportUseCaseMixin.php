<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingServiceReportUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingServiceReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingServiceReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingServiceReportUseCase
     */
    protected $createDwsBillingServiceReportUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingServiceReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingServiceReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingServiceReportUseCase::class,
                fn () => $self->createDwsBillingServiceReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingServiceReportUseCase = Mockery::mock(
                CreateDwsBillingServiceReportUseCase::class
            );
        });
    }
}
