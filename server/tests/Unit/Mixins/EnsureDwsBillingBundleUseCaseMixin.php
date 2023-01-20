<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EnsureDwsBillingBundleUseCase;

/**
 * {@link \UseCase\Billing\EnsureDwsBillingBundleUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EnsureDwsBillingBundleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EnsureDwsBillingBundleUseCase
     */
    protected $ensureDwsBillingBundleUseCase;

    /**
     * {@link \UseCase\Billing\EnsureDwsBillingBundleUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEnsureDwsBillingBundleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EnsureDwsBillingBundleUseCase::class,
                fn () => $self->ensureDwsBillingBundleUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ensureDwsBillingBundleUseCase = Mockery::mock(
                EnsureDwsBillingBundleUseCase::class
            );
        });
    }
}
