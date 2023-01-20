<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EnsureLtcsBillingBundleUseCase;

/**
 * {@link \UseCase\Billing\EnsureLtcsBillingBundleUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EnsureLtcsBillingBundleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EnsureLtcsBillingBundleUseCase
     */
    protected $ensureLtcsBillingBundleUseCase;

    /**
     * {@link \UseCase\Billing\EnsureLtcsBillingBundleUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEnsureLtcsBillingBundleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EnsureLtcsBillingBundleUseCase::class,
                fn () => $self->ensureLtcsBillingBundleUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ensureLtcsBillingBundleUseCase = Mockery::mock(
                EnsureLtcsBillingBundleUseCase::class
            );
        });
    }
}
