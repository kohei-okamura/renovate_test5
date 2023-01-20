<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\LtcsProjectServiceMenuRepository;
use Mockery;

/**
 * LtcsProjectServiceMenuRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsProjectServiceMenuRepositoryMixin
{
    /**
     * @var \Domain\Project\LtcsProjectServiceMenuRepository|\Mockery\MockInterface
     */
    protected $ltcsProjectServiceMenuRepository;

    /**
     * LtcsProjectServiceMenuRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsProjectServiceMenuRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsProjectServiceMenuRepository::class, fn () => $self->ltcsProjectServiceMenuRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsProjectServiceMenuRepository = Mockery::mock(LtcsProjectServiceMenuRepository::class);
        });
    }
}
