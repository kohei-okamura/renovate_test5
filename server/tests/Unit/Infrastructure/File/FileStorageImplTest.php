<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\File;

use Domain\File\FileInputStream;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Infrastructure\File\FileStorageImpl;
use SplFileInfo;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FilesystemMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\File\FileStorageImpl} のテスト.
 */
final class FileStorageImplTest extends Test
{
    use ConfigMixin;
    use FilesystemMixin;
    use TemporaryFilesMixin;
    use UnitSupport;

    private const EXAMPLE_FILE = 'testing/example.jpeg';
    private const NO_WHERE_FILE = 'no/where/file.png';

    private FileStorageImpl $storage;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FileStorageImplTest $self): void {
            $self->setupFakeStorage('s3');

            $self->config->allows('get')->with('zinger.file.storage')->andReturn('s3');
            $self->config->allows('get')->with('zinger.path.temp')->andReturn(sys_get_temp_dir());

            $self->storage = app(FileStorageImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fetch(): void
    {
        $this->should('return local file path', function (): void {
            $example = resource_path(self::EXAMPLE_FILE);
            $path = $this->filesystem->disk('s3')->putFile('example', new File($example));
            $this->temporaryFiles->expects('create')->with('zinger-', '.jpg')->passthru();

            $option = $this->storage->fetch($path);

            $this->assertSome($option, function (SplFileInfo $file) use ($example): void {
                $this->assertStringEqualsFile($example, file_get_contents($file->getPathname()));
            });
        });
        $this->should('return None when the file not exists', function (): void {
            $option = $this->storage->fetch(self::NO_WHERE_FILE);

            $this->filesystem->disk('s3')->assertMissing(self::NO_WHERE_FILE);
            $this->assertNone($option);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fetchStream(): void
    {
        $this->should('return readable stream', function (): void {
            $example = resource_path(self::EXAMPLE_FILE);
            $path = $this->filesystem->disk('s3')->putFile('example', new File($example));

            $option = $this->storage->fetchStream($path);

            $this->assertSome($option, function ($stream) use ($example): void {
                try {
                    $this->assertStringEqualsFile($example, stream_get_contents($stream));
                } finally {
                    fclose($stream);
                }
            });
        });
        $this->should('return None when the file not exists', function (): void {
            $option = $this->storage->fetchStream(self::NO_WHERE_FILE);

            $this->filesystem->disk('s3')->assertMissing(self::NO_WHERE_FILE);
            $this->assertNone($option);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('store the file to storage', function (): void {
            $file = FileInputStream::fromFile(UploadedFile::fake()->create('example.pdf'));

            $option = $this->storage->store('example', $file);

            $this->assertSome($option, function (string $path): void {
                $this->filesystem->disk('s3')->assertExists($path);
            });
        });
        $this->should('return None when failed to store the file', function (): void {
            $this->filesystem->expects('disk')->andReturn($this->disk);
            $this->disk->expects('put')->andReturn(false);
            $file = FileInputStream::fromFile(UploadedFile::fake()->create('example.pdf'));

            $option = $this->storage->store('example', $file);

            $this->assertNone($option);
        });
    }
}
