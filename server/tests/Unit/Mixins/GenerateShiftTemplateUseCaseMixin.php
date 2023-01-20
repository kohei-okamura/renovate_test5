<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\GenerateShiftTemplateUseCase;

/**
 * GenerateShiftTemplateUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GenerateShiftTemplateUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\GenerateShiftTemplateUseCase
     */
    protected $generateShiftTemplateUseCase;

    /**
     * GenerateShiftTemplateUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGenerateShiftTemplateUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GenerateShiftTemplateUseCase::class, fn () => $self->generateShiftTemplateUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->generateShiftTemplateUseCase = Mockery::mock(GenerateShiftTemplateUseCase::class);
        });
    }
}
