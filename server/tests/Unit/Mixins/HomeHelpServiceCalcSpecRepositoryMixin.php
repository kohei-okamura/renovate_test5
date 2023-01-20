<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\HomeHelpServiceCalcSpecRepository;
use Mockery;

/**
 * HomeHelpServiceCalcSpecRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait HomeHelpServiceCalcSpecRepositoryMixin
{
    /**
     * @var \Domain\Office\HomeHelpServiceCalcSpecRepository|\Mockery\MockInterface
     */
    protected $homeHelpServiceCalcSpecRepository;

    /**
     * HomeHelpServiceCalcSpecRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinHomeHelpServiceCalcSpecRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(HomeHelpServiceCalcSpecRepository::class, fn () => $self->homeHelpServiceCalcSpecRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->homeHelpServiceCalcSpecRepository = Mockery::mock(HomeHelpServiceCalcSpecRepository::class);
        });
    }
}
