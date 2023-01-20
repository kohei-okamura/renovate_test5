<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\CreateLtcsProjectUseCase;

/**
 * CreateLtcsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\CreateLtcsProjectUseCase
     */
    protected $createLtcsProjectUseCase;

    /**
     * CreateLtcsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateLtcsProjectUseCase::class, fn () => $self->createLtcsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createLtcsProjectUseCase = Mockery::mock(CreateLtcsProjectUseCase::class);
        });
    }
}
