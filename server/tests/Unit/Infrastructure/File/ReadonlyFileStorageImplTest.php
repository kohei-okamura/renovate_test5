<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\File;

use Domain\Common\Carbon;
use Illuminate\Http\File;
use Infrastructure\File\ReadonlyFileStorageImpl;
use SplFileInfo;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FilesystemMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\File\ReadonlyFileStorageImpl} のテスト.
 */
final class ReadonlyFileStorageImplTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use FilesystemMixin;
    use TemporaryFilesMixin;
    use UnitSupport;

    private const EXAMPLE_FILE = 'testing/example.jpeg';
    private const NO_WHERE_FILE = 'no/where/file.png';

    private ReadonlyFileStorageImpl $storage;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ReadonlyFileStorageImplTest $self): void {
            $self->setupFakeStorage('readonly_s3');

            $self->config->allows('get')->with('zinger.file.readonly_storage')->andReturn('readonly_s3');
            $self->config->allows('get')->with('zinger.path.temp')->andReturn(sys_get_temp_dir());

            $self->storage = app(ReadonlyFileStorageImpl::class);
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
            $path = $this->filesystem->disk('readonly_s3')->putFile('example', new File($example));
            $this->temporaryFiles->expects('create')->with('zinger-', '.jpg')->passthru();

            $option = $this->storage->fetch($path);

            $this->assertSome($option, function (SplFileInfo $file) use ($example): void {
                $this->assertStringEqualsFile($example, file_get_contents($file->getPathname()));
            });
        });
        $this->should('return None when the file not exists', function (): void {
            $option = $this->storage->fetch(self::NO_WHERE_FILE);

            $this->filesystem->disk('readonly_s3')->assertMissing(self::NO_WHERE_FILE);
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
            $path = $this->filesystem->disk('readonly_s3')->putFile('example', new File($example));

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

            $this->filesystem->disk('readonly_s3')->assertMissing(self::NO_WHERE_FILE);
            $this->assertNone($option);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getTemporaryUrl(): void
    {
        $this->should('return string', function (): void {
            $this->filesystem->expects('disk')->andReturn($this->disk);
            $this->disk->expects('temporaryUrl')->andReturn('temporary-url');

            $this->assertSame(
                'temporary-url',
                $this->storage->getTemporaryUrl('path', Carbon::now(), 'filename')
            );
        });
        $this->should('use FileStorage with specified arguments', function (): void {
            $filename = 'ファイル名';
            $encodedFilename = rawurlencode($filename);

            $this->filesystem->expects('disk')->andReturn($this->disk);
            $this->disk->expects('temporaryUrl')
                ->with('path', equalTo(Carbon::now()), equalTo([
                    'ResponseContentDisposition' => "attachment; filename=\"[{$encodedFilename}]\"; filename*=UTF-8''{$encodedFilename}",
                ]))
                ->andReturn('temporary-url');

            $this->storage->getTemporaryUrl('path', Carbon::now(), $filename);
        });
    }
}
