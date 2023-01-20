<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\File\StorePdfUseCase;

/**
 * {@link \UseCase\File\StorePdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StorePdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\File\StorePdfUseCase
     */
    protected $storePdfUseCase;

    /**
     * StorePdfUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStorePdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(StorePdfUseCase::class, fn () => $self->storePdfUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->storePdfUseCase = Mockery::mock(StorePdfUseCase::class);
        });
    }
}
