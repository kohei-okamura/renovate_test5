<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\LtcsProjectRepository;
use Mockery;

/**
 * LtcsProjectRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsProjectRepositoryMixin
{
    /**
     * @var \Domain\Project\LtcsProjectRepository|\Mockery\MockInterface
     */
    protected $ltcsProjectRepository;

    /**
     * LtcsProjectRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsProjectRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsProjectRepository::class, fn () => $self->ltcsProjectRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsProjectRepository = Mockery::mock(LtcsProjectRepository::class);
        });
    }
}
