<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use Lib\Exceptions\TemporaryFileAccessException;
use ScalikePHP\Option;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Test;
use UseCase\File\DownloadStorageInteractor;

/**
 * {@link \UseCase\File\DownloadStorageInteractor} Test.
 */
class DownloadStorageInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use UnitSupport;

    private const STORAGE_PATH = 'aaa/bbb';

    private DownloadStorageInteractor $interactor;

    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DownloadStorageInteractorTest $self): void {
            $self->interactor = app(DownloadStorageInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('fetch the file from storage', function (): void {
            $expects = new SplFileInfo('dummy');
            $this->fileStorage
                ->expects('fetch')
                ->with(self::STORAGE_PATH)
                ->andReturn(Option::from($expects));

            $this->assertEquals($expects, $this->interactor->handle($this->context, self::STORAGE_PATH));
        });
        $this->should('throw exception when storage retutn none', function (): void {
            $this->fileStorage
                ->expects('fetch')
                ->andReturn(Option::none());

            $this->assertThrows(
                TemporaryFileAccessException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::STORAGE_PATH);
                }
            );
        });
    }
}
