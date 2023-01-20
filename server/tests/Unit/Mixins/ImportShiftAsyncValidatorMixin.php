<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Validator\ImportShiftAsyncValidator;
use Mockery;

/**
 * {@link \Domain\Validator\ImportShiftAsyncValidator} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ImportShiftAsyncValidatorMixin
{
    /**
     * @var \Domain\Validator\ImportShiftAsyncValidator|\Mockery\MockInterface
     */
    protected ImportShiftAsyncValidator $importShiftAsyncValidator;

    /**
     * ImportShiftAsyncValidator に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinImportShiftAsyncValidator(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ImportShiftAsyncValidator::class, fn () => $self->importShiftAsyncValidator);
        });
        static::beforeEachSpec(function ($self): void {
            $self->importShiftAsyncValidator = Mockery::mock(ImportShiftAsyncValidator::class);
        });
    }
}
