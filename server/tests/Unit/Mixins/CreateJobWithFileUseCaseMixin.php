<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\CreateJobWithFileUseCase;

/**
 * {@link \UseCase\Job\CreateJobWithFileUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateJobWithFileUseCaseMixin
{
    /**
     * @var Mockery\MockInterface|\UseCase\Job\CreateJobWithFileUseCase
     */
    protected $createJobWithFileUseCase;

    /**
     * CreateJobWithFileUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateJobWithFileUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateJobWithFileUseCase::class, fn () => $self->createJobWithFileUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createJobWithFileUseCase = Mockery::mock(CreateJobWithFileUseCase::class);
        });
    }
}
