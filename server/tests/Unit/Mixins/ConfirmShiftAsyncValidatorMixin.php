<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Validator\ConfirmShiftAsyncValidator;
use Mockery;

/**
 * {@link \Domain\Validator\ConfirmShiftAsyncValidator} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ConfirmShiftAsyncValidatorMixin
{
    /**
     * @var \Domain\Validator\ConfirmShiftAsyncValidator|\Mockery\MockInterface
     */
    protected ConfirmShiftAsyncValidator $confirmShiftAsyncValidator;

    /**
     * ConfirmShiftAsyncValidator に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConfirmShiftAsyncValidator(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ConfirmShiftAsyncValidator::class, fn () => $self->confirmShiftAsyncValidator);
        });
        static::beforeEachSpec(function ($self): void {
            $self->confirmShiftAsyncValidator = Mockery::mock(ConfirmShiftAsyncValidator::class);
        });
    }
}
