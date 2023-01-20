<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsCertification\FindDwsCertificationUseCase;

/**
 * FindDwsCertification Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindDwsCertificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsCertification\FindDwsCertificationUseCase
     */
    protected $findDwsCertificationUseCase;

    /**
     * FindDwsCertificationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindDwsCertificationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindDwsCertificationUseCase::class, fn () => $self->findDwsCertificationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findDwsCertificationUseCase = Mockery::mock(FindDwsCertificationUseCase::class);
        });
    }
}
