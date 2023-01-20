<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsCertification\EditDwsCertificationUseCase;

/**
 * EditDwsCertificationUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsCertificationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsCertification\EditDwsCertificationUseCase
     */
    protected $editDwsCertificationUseCase;

    /**
     * EditDwsCertificationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsCertificationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditDwsCertificationUseCase::class, fn () => $self->editDwsCertificationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editDwsCertificationUseCase = Mockery::mock(EditDwsCertificationUseCase::class);
        });
    }
}
