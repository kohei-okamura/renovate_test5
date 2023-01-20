<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingStatementListUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingStatementListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingStatementListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingStatementListUseCase
     */
    protected $createDwsBillingStatementListUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingStatementListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingStatementListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingStatementListUseCase::class,
                fn () => $self->createDwsBillingStatementListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingStatementListUseCase = Mockery::mock(
                CreateDwsBillingStatementListUseCase::class
            );
        });
    }
}
