<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Role\GetIndexRoleOptionUseCase;

/**
 * GetIndexRoleOptionUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetIndexRoleOptionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Role\GetIndexRoleOptionUseCase
     */
    protected $getIndexRoleOptionUseCase;

    /**
     * GetIndexRoleOptionUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetIndexRoleOptionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetIndexRoleOptionUseCase::class, fn () => $self->getIndexRoleOptionUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getIndexRoleOptionUseCase = Mockery::mock(GetIndexRoleOptionUseCase::class);
        });
    }
}
