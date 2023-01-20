<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\DownloadDwsProjectUseCase;

/**
 * DownloadDwsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DownloadDwsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\DownloadDwsProjectUseCase
     */
    protected $downloadDwsProjectUseCase;

    /**
     * DownloadDwsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDownloadDwsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DownloadDwsProjectUseCase::class, fn () => $self->downloadDwsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->downloadDwsProjectUseCase = Mockery::mock(DownloadDwsProjectUseCase::class);
        });
    }
}
