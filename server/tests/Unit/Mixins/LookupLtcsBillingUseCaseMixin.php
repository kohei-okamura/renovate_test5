<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupLtcsBillingUseCase;

/**
 * {@link \UseCase\Billing\LookupLtcsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupLtcsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupLtcsBillingUseCase
     */
    protected $lookupLtcsBillingUseCase;

    /**
     * {@link \UseCase\Billing\LookupLtcsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupLtcsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupLtcsBillingUseCase::class,
                fn () => $self->lookupLtcsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupLtcsBillingUseCase = Mockery::mock(
                LookupLtcsBillingUseCase::class
            );
        });
    }
}
