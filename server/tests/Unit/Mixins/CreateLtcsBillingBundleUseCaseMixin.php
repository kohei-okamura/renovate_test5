<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateLtcsBillingBundleUseCase;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingBundleUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsBillingBundleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateLtcsBillingBundleUseCase
     */
    protected $createLtcsBillingBundleUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingBundleUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsBillingBundleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateLtcsBillingBundleUseCase::class,
                fn () => $self->createLtcsBillingBundleUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createLtcsBillingBundleUseCase = Mockery::mock(
                CreateLtcsBillingBundleUseCase::class
            );
        });
    }
}
