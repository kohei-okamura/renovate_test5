<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\GetOfficeInfoUseCase;

/**
 * GetOfficeInfoUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetOfficeInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\GetOfficeInfoUseCase
     */
    protected $getOfficeInfoUseCase;

    /**
     * GetOfficeInfoUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetOfficeInfo(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetOfficeInfoUseCase::class, fn () => $self->getOfficeInfoUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getOfficeInfoUseCase = Mockery::mock(GetOfficeInfoUseCase::class);
        });
    }
}
