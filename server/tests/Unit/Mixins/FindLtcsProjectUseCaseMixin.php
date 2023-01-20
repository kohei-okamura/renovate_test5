<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\FindLtcsProjectUseCase;

/**
 * FindLtcsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindLtcsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\FindLtcsProjectUseCase
     */
    protected $findLtcsProjectUseCase;

    /**
     * FindLtcsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindLtcsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindLtcsProjectUseCase::class, fn () => $self->findLtcsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findLtcsProjectUseCase = Mockery::mock(FindLtcsProjectUseCase::class);
        });
    }
}
