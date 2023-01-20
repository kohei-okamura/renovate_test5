<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingRepositoryMixin
{
    /**
     * @var \Domain\Billing\DwsBillingRepository|\Mockery\MockInterface
     */
    protected $dwsBillingRepository;

    /**
     * {@link \Domain\Billing\DwsBillingRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsBillingRepository::class,
                fn () => $self->dwsBillingRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsBillingRepository = Mockery::mock(
                DwsBillingRepository::class
            );
        });
    }
}
