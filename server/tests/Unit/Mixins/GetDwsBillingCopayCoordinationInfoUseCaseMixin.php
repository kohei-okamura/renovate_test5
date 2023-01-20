<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase;

/**
 * {@link \UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetDwsBillingCopayCoordinationInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase
     */
    protected $getDwsBillingCopayCoordinationInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetDwsBillingCopayCoordinationInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetDwsBillingCopayCoordinationInfoUseCase::class,
                fn () => $self->getDwsBillingCopayCoordinationInfoUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getDwsBillingCopayCoordinationInfoUseCase = Mockery::mock(
                GetDwsBillingCopayCoordinationInfoUseCase::class
            );
        });
    }
}
