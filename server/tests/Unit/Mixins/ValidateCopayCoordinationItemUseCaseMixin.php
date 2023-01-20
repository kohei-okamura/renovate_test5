<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ValidateCopayCoordinationItemUseCase;

/**
 * {@link \UseCase\Billing\ValidateCopayCoordinationItemUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ValidateCopayCoordinationItemUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ValidateCopayCoordinationItemUseCase
     */
    protected $validateCopayCoordinationItemUseCase;

    /**
     * {@link \UseCase\Billing\ValidateCopayCoordinationItemUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinValidateCopayCoordinationItemUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ValidateCopayCoordinationItemUseCase::class,
                fn () => $self->validateCopayCoordinationItemUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->validateCopayCoordinationItemUseCase = Mockery::mock(
                ValidateCopayCoordinationItemUseCase::class
            );
        });
    }
}
