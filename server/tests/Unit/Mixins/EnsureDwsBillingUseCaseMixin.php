<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EnsureDwsBillingUseCase;

/**
 * {@link \UseCase\Billing\EnsureDwsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EnsureDwsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EnsureDwsBillingUseCase
     */
    protected $ensureDwsBillingUseCase;

    /**
     * {@link \UseCase\Billing\EnsureDwsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEnsureDwsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EnsureDwsBillingUseCase::class,
                fn () => $self->ensureDwsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ensureDwsBillingUseCase = Mockery::mock(
                EnsureDwsBillingUseCase::class
            );
        });
    }
}
