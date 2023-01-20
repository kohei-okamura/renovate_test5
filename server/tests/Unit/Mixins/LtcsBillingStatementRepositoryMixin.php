<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingStatementRepository;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingStatementRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingStatementRepositoryMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingStatementRepository|\Mockery\MockInterface
     */
    protected $ltcsBillingStatementRepository;

    /**
     * {@link \Domain\Billing\LtcsBillingStatementRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingStatementRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LtcsBillingStatementRepository::class,
                fn () => $self->ltcsBillingStatementRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingStatementRepository = Mockery::mock(
                LtcsBillingStatementRepository::class
            );
        });
    }
}
