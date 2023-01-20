<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\DownloadLtcsProjectUseCase;

/**
 * DownloadLtcsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DownloadLtcsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\DownloadLtcsProjectUseCase
     */
    protected $downloadLtcsProjectUseCase;

    /**
     * DownloadLtcsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDownloadLtcsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DownloadLtcsProjectUseCase::class, fn () => $self->downloadLtcsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->downloadLtcsProjectUseCase = Mockery::mock(DownloadLtcsProjectUseCase::class);
        });
    }
}
