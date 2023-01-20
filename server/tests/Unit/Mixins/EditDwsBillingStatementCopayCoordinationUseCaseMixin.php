<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingStatementCopayCoordinationUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingStatementCopayCoordinationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingStatementCopayCoordinationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingStatementCopayCoordinationUseCase
     */
    protected $editDwsBillingStatementCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingStatementCopayCoordinationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingStatementCopayCoordinationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingStatementCopayCoordinationUseCase::class,
                fn () => $self->editDwsBillingStatementCopayCoordinationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingStatementCopayCoordinationUseCase = Mockery::mock(
                EditDwsBillingStatementCopayCoordinationUseCase::class
            );
        });
    }
}
