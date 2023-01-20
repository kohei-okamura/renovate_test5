<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingCopayCoordinationUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingCopayCoordinationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingCopayCoordinationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingCopayCoordinationUseCase
     */
    protected $editDwsBillingCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingCopayCoordinationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingCopayCoordinationUseCaseMixin(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingCopayCoordinationUseCase::class,
                fn () => $self->editDwsBillingCopayCoordinationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingCopayCoordinationUseCase = Mockery::mock(
                EditDwsBillingCopayCoordinationUseCase::class
            );
        });
    }
}
