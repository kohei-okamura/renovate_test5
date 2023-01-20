<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\Contracts\Filesystem\Filesystem as Disk;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Mockery;

/**
 * {@link \Illuminate\Filesystem\FilesystemManager} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FilesystemMixin
{
    /**
     * @var \Illuminate\Filesystem\FilesystemManager|\Mockery\MockInterface
     */
    protected FilesystemManager $filesystem;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem|\Mockery\MockInterface
     */
    protected Disk $disk;

    /**
     * FilesystemManager に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFilesystem(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind('filesystem', fn () => $self->filesystem);
        });
        static::beforeEachSpec(function ($self): void {
            $self->filesystem = Mockery::mock(FilesystemManager::class, [app()])->makePartial();
            $self->disk = Mockery::mock(Disk::class);
        });
    }

    /**
     * テスト用フェイク（偽）ストレージを用意する.
     *
     * @param string $disk
     * @return void
     * @see \Illuminate\Support\Facades\Storage::fake()
     */
    protected function setupFakeStorage(string $disk): void
    {
        $root = storage_path('framework/testing/disks/' . $disk);
        (new Filesystem())->cleanDirectory($root);

        $driver = $this->filesystem->createLocalDriver(['root' => $root]);
        $this->filesystem->set($disk, $driver);
    }
}
