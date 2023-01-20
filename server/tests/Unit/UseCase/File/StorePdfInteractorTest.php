<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use Illuminate\Support\Str;
use Lib\Exceptions\FileIOException;
use ScalikePHP\Option;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\PdfCreatorMixin;
use Tests\Unit\Test;
use UseCase\File\StorePdfInteractor;

/**
 * {@link \UseCase\File\StorePdfInteractor} のテスト.
 */
final class StorePdfInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use MockeryMixin;
    use PdfCreatorMixin;
    use UnitSupport;

    private StorePdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (StorePdfInteractorTest $self): void {
            $self->pdfCreator
                ->allows('create')
                ->andReturnUsing(fn (): SplFileInfo => $self->createTemporaryFileInfoStub())
                ->byDefault();
            $self->fileStorage
                ->allows('store')
                ->andReturn(Option::some('path/to/stored-file.pdf'))
                ->byDefault();

            $self->interactor = app(StorePdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('create a temporary file using PdfCreator', function (): void {
            $params = [];
            $this->pdfCreator
                ->expects('create')
                ->with('pdfs.example', $params, 'portrait')
                ->andReturnUsing(fn (): SplFileInfo => $this->createTemporaryFileInfoStub());

            $this->interactor->handle($this->context, 'test', 'pdfs.example', $params);
        });
        $this->should('throw FileIOException when FileStorage returns None', function (): void {
            $this->fileStorage
                ->expects('store')
                ->andReturn(Option::none());

            $this->assertThrows(FileIOException::class, function (): void {
                $this->interactor->handle($this->context, 'test', 'pdfs.example', []);
            });
        });
        $this->should('return the path to stored file', function (): void {
            $expected = Str::random(32);
            $this->fileStorage->expects('store')->andReturn(Option::some($expected));

            $actual = $this->interactor->handle($this->context, 'test', 'pdfs.example', []);

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
