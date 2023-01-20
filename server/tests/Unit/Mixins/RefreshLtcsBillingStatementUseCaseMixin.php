<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RefreshLtcsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\RefreshLtcsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RefreshLtcsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RefreshLtcsBillingStatementUseCase
     */
    protected $refreshLtcsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\RefreshLtcsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRefreshLtcsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RefreshLtcsBillingStatementUseCase::class,
                fn () => $self->refreshLtcsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->refreshLtcsBillingStatementUseCase = Mockery::mock(
                RefreshLtcsBillingStatementUseCase::class
            );
        });
    }
}
