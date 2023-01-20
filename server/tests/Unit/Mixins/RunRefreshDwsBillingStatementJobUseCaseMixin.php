<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunRefreshDwsBillingStatementJobUseCase;

/**
 * {@link \UseCase\Billing\RunRefreshDwsBillingStatementJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunRefreshDwsBillingStatementJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunRefreshDwsBillingStatementJobUseCase
     */
    protected $runRefreshDwsBillingStatementJobUseCase;

    /**
     * {@link \UseCase\Billing\RunRefreshDwsBillingStatementJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunRefreshDwsBillingStatementJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunRefreshDwsBillingStatementJobUseCase::class,
                fn () => $self->runRefreshDwsBillingStatementJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runRefreshDwsBillingStatementJobUseCase = Mockery::mock(
                RunRefreshDwsBillingStatementJobUseCase::class
            );
        });
    }
}
