<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingBundleFinder;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingBundleFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingBundleFinderMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingBundleFinder|\Mockery\MockInterface
     */
    protected $ltcsBillingBundleFinder;

    /**
     * LtcsBillingBundleFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingBundleFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(LtcsBillingBundleFinder::class, fn () => $self->ltcsBillingBundleFinder);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingBundleFinder = Mockery::mock(LtcsBillingBundleFinder::class);
        });
    }
}
