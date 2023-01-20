<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\GetOfficeListUseCase;

/**
 * {@link \UseCase\Office\GetOfficeListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetOfficeListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\GetOfficeListUseCase
     */
    protected $getOfficeListUseCase;

    /**
     * GetOfficeListUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetOffice(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetOfficeListUseCase::class, fn () => $self->getOfficeListUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getOfficeListUseCase = Mockery::mock(GetOfficeListUseCase::class);
        });
    }
}
