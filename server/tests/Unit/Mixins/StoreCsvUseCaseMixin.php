<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\File\StoreCsvUseCase;

/**
 * {@link \UseCase\File\StoreCsvUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StoreCsvUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\File\StoreCsvUseCase
     */
    protected $storeCsvUseCase;

    /**
     * StoreCsvUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStoreCsvUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(StoreCsvUseCase::class, fn () => $self->storeCsvUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->storeCsvUseCase = Mockery::mock(StoreCsvUseCase::class);
        });
    }
}
