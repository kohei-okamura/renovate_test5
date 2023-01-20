<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use Domain\File\FileInputStream;
use Illuminate\Http\UploadedFile;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Test;
use UseCase\File\UploadStorageInteractor;

/**
 * {@link \UseCase\File\UploadStorageInteractor} のテスト.
 */
final class UploadStorageInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use UnitSupport;

    private UploadStorageInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UploadStorageInteractorTest $self): void {
            $self->interactor = app(UploadStorageInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the file to storage', function (): void {
            $dir = 'example';
            $stream = FileInputStream::fromFile(UploadedFile::fake()->create('example.xlsx'));
            $this->fileStorage
                ->expects('store')
                ->with($dir, $stream)
                ->andReturn(Option::some('aaa/bbb/ccc.xlsx'));

            $this->interactor->handle($this->context, $dir, $stream);
        });
        $this->should('return stored file path string', function (): void {
            $this->fileStorage->expects('store')->andReturn(Option::some('data/test.xlsx'));
            $stream = FileInputStream::fromFile(UploadedFile::fake()->create('example.xlsx'));

            $option = $this->interactor->handle($this->context, 'xxx', $stream);

            $this->assertSome($option, function (string $actual): void {
                $this->assertSame('data/test.xlsx', $actual);
            });
        });
        $this->should('return None when failed to store the file', function (): void {
            $this->fileStorage->expects('store')->andReturn(Option::none());
            $stream = FileInputStream::fromFile(UploadedFile::fake()->create('example.gif'));

            $option = $this->interactor->handle($this->context, 'zzz', $stream);

            $this->assertNone($option);
        });
    }
}
