<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\File\DownloadFileUseCase;

/**
 * {@link \UseCase\File\DownloadFileUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DownloadFileUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\File\DownloadFileUseCase
     */
    protected $downloadFileUseCase;

    /**
     * DownloadFileUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDownloadFileUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DownloadFileUseCase::class, fn () => $self->downloadFileUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->downloadFileUseCase = Mockery::mock(DownloadFileUseCase::class);
        });
    }
}
