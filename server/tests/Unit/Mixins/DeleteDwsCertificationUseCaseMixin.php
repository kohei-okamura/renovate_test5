<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsCertification\DeleteDwsCertificationUseCase;

/**
 * DeleteDwsCertificationUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DeleteDwsCertificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsCertification\DeleteDwsCertificationUseCase
     */
    protected $deleteDwsCertificationUseCase;

    /**
     * DeleteDwsCertificationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDeleteDwsCertificationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DeleteDwsCertificationUseCase::class, fn () => $self->deleteDwsCertificationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->deleteDwsCertificationUseCase = Mockery::mock(DeleteDwsCertificationUseCase::class);
        });
    }
}
