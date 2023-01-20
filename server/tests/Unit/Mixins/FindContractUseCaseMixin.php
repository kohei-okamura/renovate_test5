<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contract\FindContractUseCase;

/**
 * FindContractUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindContractUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Contract\FindContractUseCase
     */
    protected $findContractUseCase;

    /**
     * FindContractUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindContractUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindContractUseCase::class, fn () => $self->findContractUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findContractUseCase = Mockery::mock(FindContractUseCase::class);
        });
    }
}
