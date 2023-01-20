<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use ZipArchive;

/**
 * {@link \ZipArchive} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ZipArchiveMixin
{
    /**
     * @var \Mockery\MockInterface&\ZipArchive
     */
    protected ZipArchive $zipArchive;

    /**
     * ZipArchive に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinZipArchive(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ZipArchive::class, fn () => $self->zipArchive);
        });
        static::beforeEachSpec(function ($self): void {
            $self->zipArchive = Mockery::mock(ZipArchive::class);
        });
    }
}
