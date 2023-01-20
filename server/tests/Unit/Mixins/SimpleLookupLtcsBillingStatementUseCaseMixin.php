<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SimpleLookupLtcsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase
     */
    protected $simpleLookupLtcsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\SimpleLookupLtcsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSimpleLookupLtcsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                SimpleLookupLtcsBillingStatementUseCase::class,
                fn () => $self->simpleLookupLtcsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->simpleLookupLtcsBillingStatementUseCase = Mockery::mock(
                SimpleLookupLtcsBillingStatementUseCase::class
            );
        });
    }
}
