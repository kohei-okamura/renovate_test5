<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\CreateJobUseCase;

/**
 * CreateJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateJobUseCaseMixin
{
    /**
     * @var Mockery\MockInterface|\UseCase\Job\CreateJobUseCase
     */
    protected $createJobUseCase;

    /**
     * CreateJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateJobUseCase::class, fn () => $self->createJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createJobUseCase = Mockery::mock(CreateJobUseCase::class);
        });
    }
}
