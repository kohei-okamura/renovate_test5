<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase;

/**
 * {@link \UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunRefreshLtcsBillingStatementJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase
     */
    protected $runRefreshLtcsBillingStatementJobUseCase;

    /**
     * {@link \UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunRefreshLtcsBillingStatementJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunRefreshLtcsBillingStatementJobUseCase::class,
                fn () => $self->runRefreshLtcsBillingStatementJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runRefreshLtcsBillingStatementJobUseCase = Mockery::mock(
                RunRefreshLtcsBillingStatementJobUseCase::class
            );
        });
    }
}
