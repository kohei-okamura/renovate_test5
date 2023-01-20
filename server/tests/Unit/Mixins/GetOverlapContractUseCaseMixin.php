<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contract\GetOverlapContractUseCase;

/**
 * GetOverlapContractUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetOverlapContractUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\Contract\GetOverlapContractInteractor */
    protected $getOverlapContractUseCase;

    /**
     * FindContractUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetOverlapContractUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetOverlapContractUseCase::class, fn () => $self->getOverlapContractUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getOverlapContractUseCase = Mockery::mock(GetOverlapContractUseCase::class);
        });
    }
}
