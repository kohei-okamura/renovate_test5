<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Contract\ContractFinder;
use Mockery;

/**
 * ContractFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ContractFinderMixin
{
    /**
     * @var \Domain\Contract\ContractFinder|\Mockery\MockInterface
     */
    protected $contractFinder;

    /**
     * ContractFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinContractFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ContractFinder::class, fn () => $self->contractFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->contractFinder = Mockery::mock(ContractFinder::class);
        });
    }
}
