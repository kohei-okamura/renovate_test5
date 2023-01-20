<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingStatementElementListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementElementListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingStatementElementListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingStatementElementListUseCase
     */
    protected $buildDwsBillingStatementElementListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementElementListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingStatementElementListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingStatementElementListUseCase::class,
                fn () => $self->buildDwsBillingStatementElementListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingStatementElementListUseCase = Mockery::mock(
                BuildDwsBillingStatementElementListUseCase::class
            );
        });
    }
}
