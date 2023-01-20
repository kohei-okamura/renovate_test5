<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateLtcsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateLtcsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateLtcsBillingStatementUseCase
     */
    protected $updateLtcsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateLtcsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateLtcsBillingStatementUseCase::class,
                fn () => $self->updateLtcsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateLtcsBillingStatementUseCase = Mockery::mock(
                UpdateLtcsBillingStatementUseCase::class
            );
        });
    }
}
