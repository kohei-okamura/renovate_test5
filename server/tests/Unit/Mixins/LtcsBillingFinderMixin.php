<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingFinder;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingFinderMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingFinder|\Mockery\MockInterface
     */
    protected $ltcsBillingFinder;

    /**
     * {@link \Domain\Billing\LtcsBillingFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LtcsBillingFinder::class,
                fn () => $self->ltcsBillingFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingFinder = Mockery::mock(
                LtcsBillingFinder::class
            );
        });
    }
}
