<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\GetIndexOfficeOptionUseCase;

/**
 * GetIndexOfficeOptionUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetIndexOfficeOptionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\GetIndexOfficeOptionUseCase
     */
    protected $getIndexOfficeOptionUseCase;

    /**
     * GetIndexOfficeOptionUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetIndexOfficeOptionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetIndexOfficeOptionUseCase::class, fn () => $self->getIndexOfficeOptionUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getIndexOfficeOptionUseCase = Mockery::mock(GetIndexOfficeOptionUseCase::class);
        });
    }
}
