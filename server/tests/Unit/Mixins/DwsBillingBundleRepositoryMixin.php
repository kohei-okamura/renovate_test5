<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingBundleRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingBundleRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingBundleRepositoryMixin
{
    /**
     * @var \Domain\Billing\DwsBillingBundleRepository|\Mockery\MockInterface
     */
    protected $dwsBillingBundleRepository;

    /**
     * {@link \Domain\Billing\DwsBillingBundleRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingBundleRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsBillingBundleRepository::class,
                fn () => $self->dwsBillingBundleRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsBillingBundleRepository = Mockery::mock(
                DwsBillingBundleRepository::class
            );
        });
    }
}
