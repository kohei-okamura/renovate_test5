<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingStatementCopayCoordinationStatusUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingStatementCopayCoordinationStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingStatementCopayCoordinationStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingStatementCopayCoordinationStatusUseCase
     */
    protected $editDwsBillingStatementCopayCoordinationStatusUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingStatementCopayCoordinationStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingStatementCopayCoordinationStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingStatementCopayCoordinationStatusUseCase::class,
                fn () => $self->editDwsBillingStatementCopayCoordinationStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingStatementCopayCoordinationStatusUseCase = Mockery::mock(
                EditDwsBillingStatementCopayCoordinationStatusUseCase::class
            );
        });
    }
}
