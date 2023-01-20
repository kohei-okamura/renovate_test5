<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingFinder;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingFinderMixin
{
    /**
     * @var \Domain\Billing\DwsBillingFinder|\Mockery\MockInterface
     */
    protected $dwsBillingFinder;

    /**
     * {@link \Domain\Billing\DwsBillingFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsBillingFinder::class,
                fn () => $self->dwsBillingFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsBillingFinder = Mockery::mock(
                DwsBillingFinder::class
            );
        });
    }
}
