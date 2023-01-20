<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupLtcsBillingBundleUseCase;

/**
 * {@link \UseCase\Billing\LookupLtcsBillingBundleUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupLtcsBillingBundleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupLtcsBillingBundleUseCase
     */
    protected $lookupLtcsBillingBundleUseCase;

    /**
     * {@link \UseCase\Billing\LookupLtcsBillingBundleUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupLtcsBillingBundleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupLtcsBillingBundleUseCase::class,
                fn () => $self->lookupLtcsBillingBundleUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupLtcsBillingBundleUseCase = Mockery::mock(
                LookupLtcsBillingBundleUseCase::class
            );
        });
    }
}
