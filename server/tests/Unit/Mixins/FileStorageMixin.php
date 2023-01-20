<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\File\FileStorage;
use Mockery;

/**
 * {@link \Domain\File\FileStorage} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FileStorageMixin
{
    /**
     * @var \Domain\File\FileStorage|\Mockery\MockInterface
     */
    protected FileStorage $fileStorage;

    /**
     * FileStorage に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFileStorage(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FileStorage::class, fn () => $self->fileStorage);
        });
        static::beforeEachSpec(function ($self): void {
            $self->fileStorage = Mockery::mock(FileStorage::class);
        });
    }
}
