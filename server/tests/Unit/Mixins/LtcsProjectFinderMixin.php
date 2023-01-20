<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\LtcsProjectFinder;
use Mockery;

/**
 * LtcsProjectFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsProjectFinderMixin
{
    /**
     * @var \Domain\Project\LtcsProjectFinder|\Mockery\MockInterface
     */
    protected $ltcsProjectFinder;

    /**
     * LtcsProjectFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsProjectFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsProjectFinder::class, fn () => $self->ltcsProjectFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsProjectFinder = Mockery::mock(LtcsProjectFinder::class);
        });
    }
}
