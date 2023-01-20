<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * {@link \UseCase\UserBilling\LookupUserBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupUserBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\LookupUserBillingUseCase
     */
    protected $lookupUserBillingUseCase;

    /**
     * {@link \UseCase\UserBilling\LookupUserBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupUserBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupUserBillingUseCase::class,
                fn () => $self->lookupUserBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupUserBillingUseCase = Mockery::mock(
                LookupUserBillingUseCase::class
            );
        });
    }
}
