<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\FindOfficeGroupUseCase;

/**
 * FindOfficeGroupUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindOfficeGroupUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\FindOfficeGroupUseCase
     */
    protected $findOfficeGroupUseCase;

    /**
     * FindOfficeGroupUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindOfficeGroupUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindOfficeGroupUseCase::class, fn () => $self->findOfficeGroupUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findOfficeGroupUseCase = Mockery::mock(FindOfficeGroupUseCase::class);
        });
    }
}
