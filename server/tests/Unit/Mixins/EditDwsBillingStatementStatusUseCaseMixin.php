<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingStatementStatusUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingStatementStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingStatementStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingStatementStatusUseCase
     */
    protected $editDwsBillingStatementStatusUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingStatementStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingStatementStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingStatementStatusUseCase::class,
                fn () => $self->editDwsBillingStatementStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingStatementStatusUseCase = Mockery::mock(
                EditDwsBillingStatementStatusUseCase::class
            );
        });
    }
}
