<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\RunJobUseCase;

/**
 * RunJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Job\RunJobUseCase
     */
    protected $runJobUseCase;

    /**
     * RunJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunJobUseCase::class, fn () => $self->runJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runJobUseCase = Mockery::mock(RunJobUseCase::class);
        });
    }
}
