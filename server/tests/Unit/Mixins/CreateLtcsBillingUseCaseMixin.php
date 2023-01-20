<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateLtcsBillingUseCase;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateLtcsBillingUseCase
     */
    protected $createLtcsBillingUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateLtcsBillingUseCase::class,
                fn () => $self->createLtcsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createLtcsBillingUseCase = Mockery::mock(
                CreateLtcsBillingUseCase::class
            );
        });
    }
}
