<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\LookupDwsAreaGradeUseCase;

/**
 * LookupDwsAreaGradeUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsAreaGradeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\LookupDwsAreaGradeUseCase
     */
    protected $lookupDwsAreaGradeUseCase;

    /**
     * LookupDwsAreaGradeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsAreaGrade(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupDwsAreaGradeUseCase::class, fn () => $self->lookupDwsAreaGradeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupDwsAreaGradeUseCase = Mockery::mock(LookupDwsAreaGradeUseCase::class);
        });
    }
}
