<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\LookupLtcsAreaGradeUseCase;

/**
 * LookupLtcsAreaGradeUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupLtcsAreaGradeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\LookupLtcsAreaGradeUseCase
     */
    protected $lookupLtcsAreaGradeUseCase;

    /**
     * LookupLtcsAreaGradeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupLtcsAreaGrade(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupLtcsAreaGradeUseCase::class, fn () => $self->lookupLtcsAreaGradeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupLtcsAreaGradeUseCase = Mockery::mock(LookupLtcsAreaGradeUseCase::class);
        });
    }
}
