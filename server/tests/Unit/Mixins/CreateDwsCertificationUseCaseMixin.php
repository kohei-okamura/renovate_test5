<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsCertification\CreateDwsCertificationUseCase;

/**
 * CreateDwsCertificationUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsCertificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsCertification\CreateDwsCertificationUseCase
     */
    protected $createDwsCertificationUseCase;

    /**
     * CreateDwsCertificationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsCertificationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateDwsCertificationUseCase::class, fn () => $self->createDwsCertificationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createDwsCertificationUseCase = Mockery::mock(CreateDwsCertificationUseCase::class);
        });
    }
}
