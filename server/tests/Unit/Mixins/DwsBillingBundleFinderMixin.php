<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingBundleFinder;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingBundleFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingBundleFinderMixin
{
    /**
     * @var \Domain\Billing\DwsBillingBundleFinder|\Mockery\MockInterface
     */
    protected $dwsBillingBundleFinder;

    /**
     * {@link \Domain\Billing\DwsBillingBundleFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingBundleFinderMixin(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(DwsBillingBundleFinder::class, fn () => $self->dwsBillingBundleFinder);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsBillingBundleFinder = Mockery::mock(DwsBillingBundleFinder::class);
        });
    }
}
