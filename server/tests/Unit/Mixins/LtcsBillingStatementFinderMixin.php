<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingStatementFinder;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingStatementFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingStatementFinderMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingStatementFinder|\Mockery\MockInterface
     */
    protected $ltcsBillingStatementFinder;

    /**
     * LtcsBillingStatementFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingStatementFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(LtcsBillingStatementFinder::class, fn () => $self->ltcsBillingStatementFinder);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingStatementFinder = Mockery::mock(LtcsBillingStatementFinder::class);
        });
    }
}
