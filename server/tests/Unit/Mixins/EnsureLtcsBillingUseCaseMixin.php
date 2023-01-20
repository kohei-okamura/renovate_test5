<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EnsureLtcsBillingUseCase;

/**
 * {@link \UseCase\Billing\EnsureLtcsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EnsureLtcsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EnsureLtcsBillingUseCase
     */
    protected $ensureLtcsBillingUseCase;

    /**
     * {@link \UseCase\Billing\EnsureLtcsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEnsureLtcsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EnsureLtcsBillingUseCase::class,
                fn () => $self->ensureLtcsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ensureLtcsBillingUseCase = Mockery::mock(
                EnsureLtcsBillingUseCase::class
            );
        });
    }
}
