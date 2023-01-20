<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\CreateDwsProjectUseCase;

/**
 * CreateDwsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\CreateDwsProjectUseCase
     */
    protected $createDwsProjectUseCase;

    /**
     * CreateDwsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateDwsProjectUseCase::class, fn () => $self->createDwsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createDwsProjectUseCase = Mockery::mock(CreateDwsProjectUseCase::class);
        });
    }
}
