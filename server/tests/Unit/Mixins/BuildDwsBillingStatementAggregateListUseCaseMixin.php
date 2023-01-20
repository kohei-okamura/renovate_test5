<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingStatementAggregateListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementAggregateListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingStatementAggregateListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingStatementAggregateListUseCase
     */
    protected $buildDwsBillingStatementAggregateListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementAggregateListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingStatementAggregateListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingStatementAggregateListUseCase::class,
                fn () => $self->buildDwsBillingStatementAggregateListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingStatementAggregateListUseCase = Mockery::mock(
                BuildDwsBillingStatementAggregateListUseCase::class
            );
        });
    }
}
