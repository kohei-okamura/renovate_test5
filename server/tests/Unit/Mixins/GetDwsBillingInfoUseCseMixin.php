<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\GetDwsBillingInfoUseCase;

/**
 * GetDwsBillingInfoUseCse Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetDwsBillingInfoUseCseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\GetDwsBillingInfoUseCase
     */
    protected $getDwsBillingStatementInfoUseCase;

    /**
     * {@link \UseCase\Billing\GetDwsBillingInfoUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetDwsBillingInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetDwsBillingInfoUseCase::class, fn () => $self->getDwsBillingStatementInfoUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getDwsBillingStatementInfoUseCase = Mockery::mock(GetDwsBillingInfoUseCase::class);
        });
    }
}
