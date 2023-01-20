<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingBundleRepository;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingBundleRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingBundleRepositoryMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingBundleRepository|\Mockery\MockInterface
     */
    protected $ltcsBillingBundleRepository;

    /**
     * LtcsBillingBundleRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingBundleRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(LtcsBillingBundleRepository::class, fn () => $self->ltcsBillingBundleRepository);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingBundleRepository = Mockery::mock(LtcsBillingBundleRepository::class);
        });
    }
}
