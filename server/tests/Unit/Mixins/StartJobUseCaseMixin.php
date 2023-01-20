<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\StartJobUseCase;

/**
 * StartJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StartJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Job\StartJobUseCase
     */
    protected $startJobUseCase;

    /**
     * StartJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStartJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StartJobUseCase::class, fn () => $self->startJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->startJobUseCase = Mockery::mock(StartJobUseCase::class);
        });
    }
}
