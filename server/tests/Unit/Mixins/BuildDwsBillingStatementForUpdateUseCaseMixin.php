<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingStatementForUpdateUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase
     */
    protected $buildDwsBillingStatementForUpdateUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementForUpdateUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingStatementForUpdateUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingStatementForUpdateUseCase::class,
                fn () => $self->buildDwsBillingStatementForUpdateUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingStatementForUpdateUseCase = Mockery::mock(
                BuildDwsBillingStatementForUpdateUseCase::class
            );
        });
    }
}
