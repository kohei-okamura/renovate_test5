<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Concretes;

use App\Concretes\PdfCreatorImpl;
use SplFileInfo;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SnappyMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Mixins\ViewMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Concretes\PdfCreatorImpl} のテスト.
 */
final class PdfCreatorImplTest extends Test
{
    use ConfigMixin;
    use SnappyMixin;
    use MockeryMixin;
    use TemporaryFilesMixin;
    use UnitSupport;
    use ViewMixin;

    private const ORIENTATION = 'portrait';

    private PdfCreatorImpl $creator;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (PdfCreatorImplTest $self): void {
            $self->temporaryFiles
                ->allows('create')
                ->andReturnUsing(fn (): SplFileInfo => $self->createTemporaryFileInfoStub())
                ->byDefault();

            $self->snappy->allows('setOption')->andReturnSelf()->byDefault();
            $self->snappy->allows('loadHTML')->andReturnSelf()->byDefault();
            $self->snappy->allows('setPaper')->andReturnSelf()->byDefault();
            $self->snappy->allows('save')->andReturnSelf()->byDefault();
            $self->view->allows('make')->andReturn('')->byDefault();
            $self->view->allows('addNamespace')->andReturn('')->byDefault();

            $self->creator = app(PdfCreatorImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('create a temporary file', function (): void {
            $file = $this->createTemporaryFileInfoStub();
            $this->temporaryFiles->expects('create')->with('pdf-', '.pdf')->andReturn($file);

            $this->creator->create('path/to/template', ['foo' => 'bar'], self::ORIENTATION);
        });
        $this->should('create pdf with arguments', function (): void {
            $template = 'path/to/template';
            $params = ['foo' => 'bar'];
            $this->snappy->expects('loadHTML')->with('')->andReturnSelf();

            $this->creator->create($template, $params, self::ORIENTATION);
        });
        $this->should('output pdf to the temporary file', function (): void {
            $file = $this->createTemporaryFileInfoStub();
            $this->temporaryFiles->expects('create')->with('pdf-', '.pdf')->andReturn($file);
            $this->snappy->expects('save')->with($file->getPathname(), true)->andReturnSelf();

            $this->creator->create('path/to/template', ['foo' => 'bar'], self::ORIENTATION);
        });
        $this->should('return the temporary file', function (): void {
            $expected = $this->createTemporaryFileInfoStub();
            $this->temporaryFiles->expects('create')->with('pdf-', '.pdf')->andReturn($expected);

            $actual = $this->creator->create('path/to/template', ['foo' => 'bar'], self::ORIENTATION);

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
