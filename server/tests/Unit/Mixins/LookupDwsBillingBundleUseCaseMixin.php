<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupDwsBillingBundleUseCase;

/**
 * {@link \UseCase\Billing\LookupDwsBillingBundleUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsBillingBundleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupDwsBillingBundleUseCase
     */
    protected $lookupDwsBillingBundleUseCase;

    /**
     * {@link \UseCase\Billing\LookupDwsBillingBundleUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsBillingBundleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupDwsBillingBundleUseCase::class,
                fn () => $self->lookupDwsBillingBundleUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupDwsBillingBundleUseCase = Mockery::mock(
                LookupDwsBillingBundleUseCase::class
            );
        });
    }
}
