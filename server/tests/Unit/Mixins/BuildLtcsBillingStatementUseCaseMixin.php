<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildLtcsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\BuildLtcsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildLtcsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildLtcsBillingStatementUseCase
     */
    protected $buildLtcsBillingStatementUseCase;

    /**
     * {@link \UseCase\Billing\BuildLtcsBillingStatementUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildLtcsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildLtcsBillingStatementUseCase::class,
                fn () => $self->buildLtcsBillingStatementUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildLtcsBillingStatementUseCase = Mockery::mock(
                BuildLtcsBillingStatementUseCase::class
            );
        });
    }
}
