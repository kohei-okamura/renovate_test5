<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateLtcsBillingStatementStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase
     */
    protected $updateLtcsBillingStatementStatusUseCase;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateLtcsBillingStatementStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateLtcsBillingStatementStatusUseCase::class,
                fn () => $self->updateLtcsBillingStatementStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateLtcsBillingStatementStatusUseCase = Mockery::mock(
                UpdateLtcsBillingStatementStatusUseCase::class
            );
        });
    }
}
