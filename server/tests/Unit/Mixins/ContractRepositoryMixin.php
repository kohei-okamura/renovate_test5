<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Contract\ContractRepository;
use Mockery;

/**
 * ContractRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ContractRepositoryMixin
{
    /**
     * @var \Domain\Contract\ContractRepository|\Mockery\MockInterface
     */
    protected $contractRepository;

    /**
     * ContractRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinContractRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ContractRepository::class, fn () => $self->contractRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->contractRepository = Mockery::mock(ContractRepository::class);
        });
    }
}
