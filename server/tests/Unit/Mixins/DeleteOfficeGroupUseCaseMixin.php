<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\DeleteOfficeGroupUseCase;

/**
 * DeleteOfficeGroupUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DeleteOfficeGroupUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\DeleteOfficeGroupUseCase
     */
    protected $deleteOfficeGroupUseCase;

    /**
     * DeleteOfficeGroupUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDeleteOfficeGroupUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DeleteOfficeGroupUseCase::class, fn () => $self->deleteOfficeGroupUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->deleteOfficeGroupUseCase = Mockery::mock(DeleteOfficeGroupUseCase::class);
        });
    }
}
