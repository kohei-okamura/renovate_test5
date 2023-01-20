<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\GetIndexOfficeGroupOptionUseCase;

/**
 * GetIndexOfficeGroupOptionUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetIndexOfficeGroupOptionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\GetIndexOfficeGroupOptionUseCase
     */
    protected $getIndexOfficeGroupOptionUseCase;

    /**
     * GetIndexOfficeGroupOptionUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetIndexOfficeGroupOptionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetIndexOfficeGroupOptionUseCase::class, fn () => $self->getIndexOfficeGroupOptionUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getIndexOfficeGroupOptionUseCase = Mockery::mock(GetIndexOfficeGroupOptionUseCase::class);
        });
    }
}
