<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateLtcsBillingStatusUseCase;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateLtcsBillingStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateLtcsBillingStatusUseCase
     */
    protected $updateLtcsBillingStatusUseCase;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateLtcsBillingStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateLtcsBillingStatusUseCase::class,
                fn () => $self->updateLtcsBillingStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateLtcsBillingStatusUseCase = Mockery::mock(
                UpdateLtcsBillingStatusUseCase::class
            );
        });
    }
}
