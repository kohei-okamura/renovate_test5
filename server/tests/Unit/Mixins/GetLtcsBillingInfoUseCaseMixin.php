<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetLtcsBillingInfoUseCase;

/**
 * {@link \UseCase\Billing\GetLtcsBillingInfoUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsBillingInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetLtcsBillingInfoUseCase
     */
    protected $getLtcsBillingInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetLtcsBillingInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsBillingInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetLtcsBillingInfoUseCase::class,
                fn () => $self->getLtcsBillingInfoUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getLtcsBillingInfoUseCase = Mockery::mock(
                GetLtcsBillingInfoUseCase::class
            );
        });
    }
}
