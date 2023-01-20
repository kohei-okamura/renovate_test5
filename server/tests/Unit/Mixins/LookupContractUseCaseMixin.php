<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contract\LookupContractUseCase;

/**
 * LookupContractUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupContractUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Contract\LookupContractUseCase
     */
    protected $lookupContractUseCase;

    /**
     * LookupContractUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupContract(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupContractUseCase::class, fn () => $self->lookupContractUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupContractUseCase = Mockery::mock(LookupContractUseCase::class);
        });
    }
}
