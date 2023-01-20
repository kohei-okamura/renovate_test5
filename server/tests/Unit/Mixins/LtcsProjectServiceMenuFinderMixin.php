<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\LtcsProjectServiceMenuFinder;
use Mockery;

/**
 * LtcsProjectServiceMenuFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsProjectServiceMenuFinderMixin
{
    /**
     * @var \Domain\Project\LtcsProjectServiceMenuFinder|\Mockery\MockInterface
     */
    protected $ltcsProjectServiceMenuFinder;

    /**
     * LtcsProjectServiceMenuFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsProjectServiceMenuFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsProjectServiceMenuFinder::class, fn () => $self->ltcsProjectServiceMenuFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsProjectServiceMenuFinder = Mockery::mock(LtcsProjectServiceMenuFinder::class);
        });
    }
}
