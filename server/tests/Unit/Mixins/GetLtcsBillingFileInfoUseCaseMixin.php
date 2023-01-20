<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetLtcsBillingFileInfoUseCase;

/**
 * {@link \UseCase\Billing\GetLtcsBillingFileInfoUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsBillingFileInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetLtcsBillingFileInfoUseCase
     */
    protected $getLtcsBillingFileInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetLtcsBillingFileInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsBillingFileInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetLtcsBillingFileInfoUseCase::class,
                fn () => $self->getLtcsBillingFileInfoUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getLtcsBillingFileInfoUseCase = Mockery::mock(
                GetLtcsBillingFileInfoUseCase::class
            );
        });
    }
}
