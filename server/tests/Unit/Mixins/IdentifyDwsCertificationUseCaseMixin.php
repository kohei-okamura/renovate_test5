<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;

/**
 * {@link \UseCase\DwsCertification\IdentifyDwsCertificationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyDwsCertificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsCertification\IdentifyDwsCertificationUseCase
     */
    protected $identifyDwsCertificationUseCase;

    /**
     * {@link \UseCase\DwsCertification\IdentifyDwsCertificationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyDwsCertificationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(IdentifyDwsCertificationUseCase::class, fn () => $self->identifyDwsCertificationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->identifyDwsCertificationUseCase = Mockery::mock(IdentifyDwsCertificationUseCase::class);
        });
    }
}
