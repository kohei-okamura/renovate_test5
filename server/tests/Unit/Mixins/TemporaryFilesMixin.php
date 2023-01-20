<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\File\TemporaryFiles;
use Infrastructure\File\TemporaryFilesImpl;
use Mockery;

/**
 * {@link \Domain\File\TemporaryFiles} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 * @mixin \Tests\Unit\Mixins\ConfigMixin
 */
trait TemporaryFilesMixin
{
    /**
     * @var \Domain\File\TemporaryFiles|\Mockery\MockInterface
     */
    protected TemporaryFiles $temporaryFiles;

    /**
     * TemporaryFiles に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinTemporaryFiles(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(TemporaryFiles::class, fn () => $self->temporaryFiles);
        });
        static::beforeEachSpec(function ($self): void {
            $self->temporaryFiles = Mockery::mock(TemporaryFilesImpl::class, [$self->config]);
        });
    }
}
