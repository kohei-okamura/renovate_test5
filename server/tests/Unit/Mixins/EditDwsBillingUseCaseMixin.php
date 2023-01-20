<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingUseCase
     */
    protected $editDwsBillingUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingUseCase::class,
                fn () => $self->editDwsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingUseCase = Mockery::mock(
                EditDwsBillingUseCase::class
            );
        });
    }
}
