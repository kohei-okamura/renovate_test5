<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateLtcsBillingStatementListUseCase;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingStatementListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsBillingStatementListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateLtcsBillingStatementListUseCase
     */
    protected $createLtcsBillingStatementListUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingStatementListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsBillingStatementListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateLtcsBillingStatementListUseCase::class,
                fn () => $self->createLtcsBillingStatementListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createLtcsBillingStatementListUseCase = Mockery::mock(
                CreateLtcsBillingStatementListUseCase::class
            );
        });
    }
}
