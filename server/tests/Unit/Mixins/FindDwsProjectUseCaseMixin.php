<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\FindDwsProjectUseCase;

/**
 * FindDwsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindDwsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\FindDwsProjectUseCase
     */
    protected $findDwsProjectUseCase;

    /**
     * FindDwsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindDwsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindDwsProjectUseCase::class, fn () => $self->findDwsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findDwsProjectUseCase = Mockery::mock(FindDwsProjectUseCase::class);
        });
    }
}
