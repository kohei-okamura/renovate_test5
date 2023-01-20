<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingStatementRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingStatementRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingStatementRepositoryMixin
{
    /**
     * @var \Domain\Billing\DwsBillingStatementRepository|\Mockery\MockInterface
     */
    protected $dwsBillingStatementRepository;

    /**
     * {@link \Domain\Billing\DwsBillingStatementRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingStatementRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsBillingStatementRepository::class,
                fn () => $self->dwsBillingStatementRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsBillingStatementRepository = Mockery::mock(
                DwsBillingStatementRepository::class
            );
        });
    }
}
