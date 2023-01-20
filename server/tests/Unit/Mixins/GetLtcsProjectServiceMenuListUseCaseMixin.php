<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\GetLtcsProjectServiceMenuListUseCase;

/**
 * GetLtcsProjectServiceMenuListUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsProjectServiceMenuListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\GetLtcsProjectServiceMenuListUseCase
     */
    protected $getLtcsProjectServiceMenuListUseCase;

    /**
     * GetLtcsProjectServiceMenuListUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsProjectServiceMenuListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetLtcsProjectServiceMenuListUseCase::class, fn () => $self->getLtcsProjectServiceMenuListUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getLtcsProjectServiceMenuListUseCase = Mockery::mock(GetLtcsProjectServiceMenuListUseCase::class);
        });
    }
}
