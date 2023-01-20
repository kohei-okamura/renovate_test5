<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\FindCallingUseCase;

/**
 * FindCallingUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindCallingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\FindCallingUseCase
     */
    protected $findCallingUseCase;

    /**
     * FindCallingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindCallingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindCallingUseCase::class, fn () => $self->findCallingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findCallingUseCase = Mockery::mock(FindCallingUseCase::class);
        });
    }
}
