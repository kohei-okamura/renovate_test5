<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingCopayCoordinationRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingCopayCoordinationRepositoryMixin
{
    /** @var \Domain\Billing\DwsBillingCopayCoordinationRepository|\Mockery\MockInterface */
    protected $dwsBillingCopayCoordinationRepository;

    /**
     * DwsBillingCopayCoordinationRepository に関する初期化・終了処理を登録する.
     */
    public static function mixinDwsBillingCopayCoordinationRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsBillingCopayCoordinationRepository::class, fn () => $self->dwsBillingCopayCoordinationRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsBillingCopayCoordinationRepository = Mockery::mock(DwsBillingCopayCoordinationRepository::class);
        });
    }
}
