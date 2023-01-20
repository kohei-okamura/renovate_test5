<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\FindOfficeUseCase;

/**
 * FindOfficeUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindOfficeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\FindOfficeUseCase
     */
    protected $findOfficeUseCase;

    /**
     * FindOfficeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindOfficeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindOfficeUseCase::class, fn () => $self->findOfficeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findOfficeUseCase = Mockery::mock(FindOfficeUseCase::class);
        });
    }
}
