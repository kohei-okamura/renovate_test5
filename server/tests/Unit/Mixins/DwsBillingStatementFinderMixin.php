<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingStatementFinder;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingStatementFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingStatementFinderMixin
{
    /**
     * @var \Domain\Billing\DwsBillingStatementFinder|\Mockery\MockInterface
     */
    protected $dwsBillingStatementFinder;

    /**
     * DwsBillingStatementFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingStatementFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsBillingStatementFinder::class, fn () => $self->dwsBillingStatementFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsBillingStatementFinder = Mockery::mock(DwsBillingStatementFinder::class);
        });
    }
}
