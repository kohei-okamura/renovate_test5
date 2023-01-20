<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\LtcsInsCard\LtcsInsCardFinder;
use Mockery;

/**
 * LtcsInsCardFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsInsCardFinderMixin
{
    /**
     * @var \Domain\LtcsInsCard\LtcsInsCard|\Mockery\MockInterface
     */
    protected $ltcsInsCardFinder;

    /**
     * LtcsInsCardFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsInsCardFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsInsCardFinder::class, fn () => $self->ltcsInsCardFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsInsCardFinder = Mockery::mock(LtcsInsCardFinder::class);
        });
    }
}
