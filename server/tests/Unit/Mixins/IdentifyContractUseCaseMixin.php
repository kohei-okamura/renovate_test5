<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * IdentifyContractUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyContractUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\Contract\IdentifyContractUseCase */
    protected $identifyContractUseCase;

    /**
     * IdentifyContractUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindIdentifyContractUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(IdentifyContractUseCase::class, fn () => $self->identifyContractUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->identifyContractUseCase = Mockery::mock(IdentifyContractUseCase::class);
        });
    }
}
