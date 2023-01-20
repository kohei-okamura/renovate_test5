<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RefreshDwsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\RefreshDwsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RefreshDwsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RefreshDwsBillingStatementUseCase
     */
    protected $refreshDwsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\RefreshDwsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRefreshDwsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RefreshDwsBillingStatementUseCase::class,
                fn () => $self->refreshDwsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->refreshDwsBillingStatementUseCase = Mockery::mock(
                RefreshDwsBillingStatementUseCase::class
            );
        });
    }
}
