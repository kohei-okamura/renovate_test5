<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\File;

use Infrastructure\File\TemporaryFilesImpl;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\File\TemporaryFilesImpl} のテスト.
 */
final class TemporaryFilesImplTest extends Test
{
    use ConfigMixin;
    use UnitSupport;

    private TemporaryFilesImpl $temporaryFiles;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (TemporaryFilesImplTest $self): void {
            $self->temporaryFiles = app(TemporaryFilesImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('create a new temporary file', function (): void {
            $dir = sys_get_temp_dir();
            $prefix = uniqid('prefix-') . '-';
            $suffix = uniqid('.suffix-');
            $this->config->allows('get')->with('zinger.path.temp')->andReturn($dir);

            $file = $this->temporaryFiles->create($prefix, $suffix);

            self::assertSame($dir, $file->getPath());
            self::assertStringStartsWith($prefix, $file->getBasename());
            self::assertStringEndsWith($suffix, $file->getBasename());
            self::assertSame('600', sprintf('%o', $file->getPerms() & 0777));
        });
    }
}
