<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\SimpleLookupDwsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SimpleLookupDwsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\SimpleLookupDwsBillingStatementUseCase
     */
    protected $simpleLookupDwsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\SimpleLookupDwsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSimpleLookupDwsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                SimpleLookupDwsBillingStatementUseCase::class,
                fn () => $self->simpleLookupDwsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->simpleLookupDwsBillingStatementUseCase = Mockery::mock(
                SimpleLookupDwsBillingStatementUseCase::class
            );
        });
    }
}
