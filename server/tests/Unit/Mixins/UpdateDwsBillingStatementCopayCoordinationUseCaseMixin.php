<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateDwsBillingStatementCopayCoordinationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase
     */
    protected $updateDwsBillingStatementCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateDwsBillingStatementCopayCoordinationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateDwsBillingStatementCopayCoordinationUseCase::class,
                fn () => $self->updateDwsBillingStatementCopayCoordinationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateDwsBillingStatementCopayCoordinationUseCase = Mockery::mock(
                UpdateDwsBillingStatementCopayCoordinationUseCase::class
            );
        });
    }
}
