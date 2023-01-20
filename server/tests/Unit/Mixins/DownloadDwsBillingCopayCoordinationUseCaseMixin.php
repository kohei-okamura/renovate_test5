<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase;

/**
 * {@link \UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DownloadDwsBillingCopayCoordinationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase
     */
    protected $downloadDwsBillingCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDownloadDwsBillingCopayCoordinationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DownloadDwsBillingCopayCoordinationUseCase::class,
                fn () => $self->downloadDwsBillingCopayCoordinationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->downloadDwsBillingCopayCoordinationUseCase = Mockery::mock(
                DownloadDwsBillingCopayCoordinationUseCase::class
            );
        });
    }
}
