<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\EditOfficeGroupUseCase;

/**
 * EditOfficeGroupUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditOfficeGroupUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\EditOfficeGroupUseCase
     */
    protected $editOfficeGroupUseCase;

    /**
     * EditOfficeGroupUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditOfficeGroupUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditOfficeGroupUseCase::class, fn () => $self->editOfficeGroupUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editOfficeGroupUseCase = Mockery::mock(EditOfficeGroupUseCase::class);
        });
    }
}
