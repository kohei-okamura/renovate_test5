<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\BulkCreateAttendanceUseCase;

/**
 * {@link \UseCase\Shift\BulkCreateAttendanceUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BulkCreateAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\BulkCreateAttendanceUseCase
     */
    protected $bulkCreateAttendanceUseCase;

    /**
     * BulkCreateAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBulkCreateAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(BulkCreateAttendanceUseCase::class, fn () => $self->bulkCreateAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->bulkCreateAttendanceUseCase = Mockery::mock(BulkCreateAttendanceUseCase::class);
            $self->bulkCreateAttendanceUseCase
                ->allows('handle')
                ->andReturn(null)
                ->byDefault();
        });
    }
}
