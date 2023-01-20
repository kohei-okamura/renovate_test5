<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\GenerateUserBillingNoticePdfUseCase;

/**
 * {@link \UseCase\UserBilling\GenerateUserBillingNoticePdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GenerateUserBillingNoticePdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\GenerateUserBillingNoticePdfUseCase
     */
    protected $generateUserBillingNoticePdfUseCase;

    /**
     * {@link \UseCase\UserBilling\GenerateUserBillingNoticePdfUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGenerateUserBillingNoticePdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GenerateUserBillingNoticePdfUseCase::class,
                fn () => $self->generateUserBillingNoticePdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->generateUserBillingNoticePdfUseCase = Mockery::mock(
                GenerateUserBillingNoticePdfUseCase::class
            );
        });
    }
}
