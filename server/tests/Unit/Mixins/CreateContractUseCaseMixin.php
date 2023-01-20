<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contract\CreateContractUseCase;

/**
 * CreateContractUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateContractUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Contract\CreateContractUseCase
     */
    protected $createContractUseCase;

    /**
     * CreateContractUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateContractUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateContractUseCase::class, fn () => $self->createContractUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createContractUseCase = Mockery::mock(CreateContractUseCase::class);
        });
    }
}
