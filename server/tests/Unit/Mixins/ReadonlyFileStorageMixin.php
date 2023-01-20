<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\File\ReadonlyFileStorage;
use Mockery;

/**
 * {@link \Domain\File\ReadonlyFileStorage} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ReadonlyFileStorageMixin
{
    /**
     * @var \Domain\File\ReadonlyFileStorage|\Mockery\MockInterface
     */
    protected ReadonlyFileStorage $readonlyFileStorage;

    /**
     * ReadonlyFileStorage に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFileStorage(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ReadonlyFileStorage::class, fn () => $self->readonlyFileStorage);
        });
        static::beforeEachSpec(function ($self): void {
            $self->readonlyFileStorage = Mockery::mock(ReadonlyFileStorage::class);
        });
    }
}
