<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\FindLtcsBillingUseCase;

/**
 * {@link \UseCase\Billing\FindLtcsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindLtcsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\FindLtcsBillingUseCase
     */
    protected $findLtcsBillingUseCase;

    /**
     * {@link \UseCase\Billing\FindLtcsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindLtcsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                FindLtcsBillingUseCase::class,
                fn () => $self->findLtcsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->findLtcsBillingUseCase = Mockery::mock(
                FindLtcsBillingUseCase::class
            );
        });
    }
}
