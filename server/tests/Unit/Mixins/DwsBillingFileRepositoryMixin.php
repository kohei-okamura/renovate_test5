<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingFileRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingFileRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingFileRepositoryMixin
{
    /** @var \Domain\Billing\DwsBillingFileRepository|\Mockery\MockInterface */
    protected $dwsBillingFileRepository;

    /**
     * DwsBillingFileRepository に関する初期化・終了処理を登録する.
     */
    public static function mixinDwsBillingFileRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsBillingFileRepository::class, fn () => $self->dwsBillingFileRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsBillingFileRepository = Mockery::mock(DwsBillingFileRepository::class);
        });
    }
}
