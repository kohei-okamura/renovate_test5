<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\EditOfficeUseCase;

/**
 * EditOfficeUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditOfficeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\EditOfficeUseCase
     */
    protected $editOfficeUseCase;

    /**
     * EditOfficeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditOfficeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditOfficeUseCase::class, fn () => $self->editOfficeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editOfficeUseCase = Mockery::mock(EditOfficeUseCase::class);
        });
    }
}
