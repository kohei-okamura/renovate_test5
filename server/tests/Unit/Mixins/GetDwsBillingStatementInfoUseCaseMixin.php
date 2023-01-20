<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetDwsBillingStatementInfoUseCase;

/**
 * {@link \UseCase\Billing\GetDwsBillingStatementInfoUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetDwsBillingStatementInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetDwsBillingStatementInfoUseCase
     */
    protected $getDwsBillingStatementInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetDwsBillingStatementInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetDwsBillingStatementInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetDwsBillingStatementInfoUseCase::class,
                fn () => $self->getDwsBillingStatementInfoUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getDwsBillingStatementInfoUseCase = Mockery::mock(
                GetDwsBillingStatementInfoUseCase::class
            );
        });
    }
}
