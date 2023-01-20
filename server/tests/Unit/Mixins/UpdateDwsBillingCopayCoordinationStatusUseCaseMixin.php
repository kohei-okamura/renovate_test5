<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateDwsBillingCopayCoordinationStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase
     */
    protected $updateDwsBillingCopayCoordinationStatusUseCase;

    /**
     * {@link \UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateDwsBillingCopayCoordinationStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateDwsBillingCopayCoordinationStatusUseCase::class,
                fn () => $self->updateDwsBillingCopayCoordinationStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateDwsBillingCopayCoordinationStatusUseCase = Mockery::mock(
                UpdateDwsBillingCopayCoordinationStatusUseCase::class
            );
        });
    }
}
