<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingUseCase
     */
    protected $createDwsBillingUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingUseCase::class,
                fn () => $self->createDwsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingUseCase = Mockery::mock(
                CreateDwsBillingUseCase::class
            );
        });
    }
}
