<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\LookupDwsBillingUseCase;

/**
 * {@link \UseCase\Billing\LookupDwsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\LookupDwsBillingUseCase
     */
    protected $lookupDwsBillingUseCase;

    /**
     * {@link \UseCase\Billing\LookupDwsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupDwsBillingUseCase::class,
                fn () => $self->lookupDwsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupDwsBillingUseCase = Mockery::mock(
                LookupDwsBillingUseCase::class
            );
        });
    }
}
