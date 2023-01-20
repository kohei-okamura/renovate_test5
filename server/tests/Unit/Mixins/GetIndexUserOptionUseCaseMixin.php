<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\GetIndexUserOptionUseCase;

/**
 * GetIndexUserOptionUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetIndexUserOptionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\GetIndexUserOptionUseCase
     */
    protected $getIndexUserOptionUseCase;

    /**
     * GetIndexUserOptionUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetIndexUserOptionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetIndexUserOptionUseCase::class, fn () => $self->getIndexUserOptionUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getIndexUserOptionUseCase = Mockery::mock(GetIndexUserOptionUseCase::class);
        });
    }
}
