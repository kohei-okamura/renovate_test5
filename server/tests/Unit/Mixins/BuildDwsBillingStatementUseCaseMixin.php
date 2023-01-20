<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingStatementUseCase
     */
    protected $buildDwsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingStatementUseCase::class,
                fn () => $self->buildDwsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingStatementUseCase = Mockery::mock(
                BuildDwsBillingStatementUseCase::class
            );
        });
    }
}
