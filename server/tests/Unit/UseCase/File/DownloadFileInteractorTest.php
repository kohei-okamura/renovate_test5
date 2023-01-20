<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use function app;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\File\DownloadFileInteractor;

/**
 * {@link \UseCase\File\DownloadFileInteractor}のテスト.
 */
class DownloadFileInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use MockeryMixin;
    use UnitSupport;

    private const PATH = 'exported/example.xlsx';

    private DownloadFileInteractor $interactor;
    private Option $resource;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DownloadFileInteractorTest $self): void {
            $self->interactor = app(DownloadFileInteractor::class);
            $self->resource = Option::from(tmpfile());
            $self->fileStorage
                ->allows('fetchStream')
                ->with(self::PATH)
                ->andReturn($self->resource)
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use FileStorage', function (): void {
            $this->fileStorage
                ->expects('fetchStream');

            $this->interactor->handle($this->context, self::PATH);
        });
        $this->should('return resource as Option via FileStorage', function (): void {
            $this->assertSame(
                $this->resource,
                $this->interactor->handle($this->context, self::PATH)
            );
        });
    }
}
