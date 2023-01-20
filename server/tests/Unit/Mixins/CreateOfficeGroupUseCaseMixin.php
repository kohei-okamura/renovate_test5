<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\CreateOfficeGroupUseCase;

/**
 * CreateOfficeGroupUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateOfficeGroupUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\CreateOfficeGroupUseCase
     */
    protected $createOfficeGroupUseCase;

    /**
     * CreateOfficeGroupUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateOfficeGroupUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateOfficeGroupUseCase::class, fn () => $self->createOfficeGroupUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createOfficeGroupUseCase = Mockery::mock(CreateOfficeGroupUseCase::class);
        });
    }
}
