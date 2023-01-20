<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetLtcsBillingStatementInfoUseCase;

/**
 * {@link \UseCase\Billing\GetLtcsBillingStatementInfoUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsBillingStatementInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetLtcsBillingStatementInfoUseCase
     */
    protected $getLtcsBillingStatementInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetLtcsBillingStatementInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsBillingStatementInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetLtcsBillingStatementInfoUseCase::class,
                fn () => $self->getLtcsBillingStatementInfoUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getLtcsBillingStatementInfoUseCase = Mockery::mock(
                GetLtcsBillingStatementInfoUseCase::class
            );
        });
    }
}
