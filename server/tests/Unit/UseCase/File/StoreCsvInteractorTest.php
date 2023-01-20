<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use Domain\File\FileInputStream;
use Illuminate\Support\Str;
use Lib\Csv;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\TemporaryFileAccessException;
use ScalikePHP\Option;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Test;
use UseCase\File\StoreCsvInteractor;

/**
 * {@link \UseCase\File\StoreCsvInteractor} のテスト.
 */
final class StoreCsvInteractorTest extends Test
{
    use ContextMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use MockeryMixin;
    use TemporaryFilesMixin;
    use UnitSupport;

    private StoreCsvInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (StoreCsvInteractorTest $self): void {
            $self->temporaryFiles
                ->allows('create')
                ->andReturnUsing(fn (): SplFileInfo => $self->createTemporaryFileInfoStub())
                ->byDefault();

            $self->fileStorage
                ->allows('store')
                ->andReturn(Option::some('path/to/stored-file.csv'))
                ->byDefault();

            $self->interactor = app(StoreCsvInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('create a temporary file using TemporaryFiles', function (): void {
            $this->temporaryFiles
                ->expects('create')
                ->with('awesome-', '.csv')
                ->andReturnUsing(fn (): SplFileInfo => $this->createTemporaryFileInfoStub());

            $this->interactor->handle($this->context, 'test', 'awesome-', []);
        });
        $this->should('throw TemporaryFileAccessException when TemporaryFile throws it', function (): void {
            $this->temporaryFiles
                ->expects('create')
                ->with('awesome-', '.csv')
                ->andThrow(new TemporaryFileAccessException('Failed to create temporary file'));

            $this->assertThrows(TemporaryFileAccessException::class, function (): void {
                $this->interactor->handle($this->context, 'test', 'awesome-', []);
            });
        });
        $this->should('create csv file correctly', function (): void {
            $file = $this->createTemporaryFileInfoStub();
            $this->temporaryFiles->expects('create')->andReturn($file);

            $this->interactor->handle($this->context, 'test', 'awesome-', [
                [1, 2, 3, 4, 5],
                ['A', 'B', 'C', 'D', 'E'],
                ['あ', 'い', 'う', 'え', 'お'],
            ]);

            $this->assertEquals(
                Csv::read(__DIR__ . '/StoreCsvInteractorTest.csv')->toArray(),
                Csv::read($file->getPathname())->toArray(),
            );
        });
        $this->should('store the csv to FileStorage', function (): void {
            $file = $this->createTemporaryFileInfoStub();
            $this->temporaryFiles->expects('create')->andReturn($file);
            $this->fileStorage
                ->expects('store')
                ->withArgs(function (string $dir, FileInputStream $inputStream) use ($file): bool {
                    $filepath = $file->getPathname();
                    return $dir === 'test'
                        && $inputStream->name() === basename($filepath)
                        && $inputStream->source() === $filepath;
                })
                ->andReturn(Option::some('path/to/stored-file.csv'));

            $this->interactor->handle($this->context, 'test', 'awesome-', []);
        });
        $this->should('throw FileIOException when FileStorage returns None', function (): void {
            $this->fileStorage->expects('store')->andReturn(Option::none());

            $this->assertThrows(FileIOException::class, function (): void {
                $this->interactor->handle($this->context, 'test', 'awesome-', []);
            });
        });
        $this->should('return the path to stored file', function (): void {
            $expected = Str::random(32);
            $this->fileStorage->expects('store')->andReturn(Option::some($expected));

            $actual = $this->interactor->handle($this->context, 'test', 'awesome-', []);

            $this->assertSame($expected, $actual);
        });
    }

    /**
     * テスト用の {@link \SplFileInfo} を生成する.
     *
     * @return \SplFileInfo
     */
    private function createTemporaryFileInfoStub(): SplFileInfo
    {
        $file = tempnam(sys_get_temp_dir(), 'test-');
        return new SplFileInfo($file);
    }
}
