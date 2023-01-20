<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\CreateOfficeUseCase;

/**
 * CreateOfficeUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateOfficeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\CreateOfficeUseCase
     */
    protected $createOfficeUseCase;

    /**
     * CreateOfficeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateOfficeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateOfficeUseCase::class, fn () => $self->createOfficeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createOfficeUseCase = Mockery::mock(CreateOfficeUseCase::class);
        });
    }
}
