<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingRepository;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingRepositoryMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingRepository|\Mockery\MockInterface
     */
    protected $ltcsBillingRepository;

    /**
     * {@link \Domain\Billing\LtcsBillingRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LtcsBillingRepository::class,
                fn () => $self->ltcsBillingRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingRepository = Mockery::mock(
                LtcsBillingRepository::class
            );
        });
    }
}
