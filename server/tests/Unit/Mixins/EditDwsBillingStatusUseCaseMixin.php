<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\EditDwsBillingStatusUseCase;

/**
 * {@link \UseCase\Billing\EditDwsBillingStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsBillingStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\EditDwsBillingStatusUseCase
     */
    protected $editDwsBillingStatusUseCase;

    /**
     * {@link \UseCase\Billing\EditDwsBillingStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsBillingStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditDwsBillingStatusUseCase::class,
                fn () => $self->editDwsBillingStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editDwsBillingStatusUseCase = Mockery::mock(
                EditDwsBillingStatusUseCase::class
            );
        });
    }
}
