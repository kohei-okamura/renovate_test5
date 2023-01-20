<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupLtcsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\LookupLtcsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupLtcsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupLtcsBillingStatementUseCase
     */
    protected $lookupLtcsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\LookupLtcsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupLtcsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupLtcsBillingStatementUseCase::class,
                fn () => $self->lookupLtcsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupLtcsBillingStatementUseCase = Mockery::mock(
                LookupLtcsBillingStatementUseCase::class
            );
        });
    }
}
