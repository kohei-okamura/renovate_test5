<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingServiceReportUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingServiceReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingServiceReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingServiceReportUseCase
     */
    protected $editDwsBillingServiceReportUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingServiceReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingServiceReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingServiceReportUseCase::class,
                fn () => $self->editDwsBillingServiceReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingServiceReportUseCase = Mockery::mock(
                EditDwsBillingServiceReportUseCase::class
            );
        });
    }
}
