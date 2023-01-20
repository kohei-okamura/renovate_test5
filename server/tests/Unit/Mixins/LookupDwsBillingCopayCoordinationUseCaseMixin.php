<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase;

/**
 * {@link \UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsBillingCopayCoordinationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase
     */
    protected $lookupDwsBillingCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\LookupDwsBillingCopayCoordinationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsBillingCopayCoordinationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupDwsBillingCopayCoordinationUseCase::class,
                fn () => $self->lookupDwsBillingCopayCoordinationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupDwsBillingCopayCoordinationUseCase = Mockery::mock(
                LookupDwsBillingCopayCoordinationUseCase::class
            );
        });
    }
}
