<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\RunCreateShiftTemplateJobUseCase;

/**
 * RunCreateShiftTemplateJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateShiftTemplateJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\RunCreateShiftTemplateJobUseCase
     */
    protected $runCreateShiftTemplateJobUseCase;

    /**
     * RunCreateShiftTemplateJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateShiftTemplateJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunCreateShiftTemplateJobUseCase::class, fn () => $self->runCreateShiftTemplateJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runCreateShiftTemplateJobUseCase = Mockery::mock(RunCreateShiftTemplateJobUseCase::class);
        });
    }
}
