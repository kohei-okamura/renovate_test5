<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsCertification\LookupDwsCertificationUseCase;

/**
 * LookupDwsCertificationUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsCertificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsCertification\LookupDwsCertificationUseCase
     */
    protected $lookupDwsCertificationUseCase;

    /**
     * LookupDwsCertificationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsCertification(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupDwsCertificationUseCase::class, function () use ($self) {
                return $self->lookupDwsCertificationUseCase;
            });
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupDwsCertificationUseCase = Mockery::mock(LookupDwsCertificationUseCase::class);
        });
    }
}
