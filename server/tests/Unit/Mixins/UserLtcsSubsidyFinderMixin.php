<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserLtcsSubsidyFinder;
use Mockery;

/**
 * {@link \Domain\User\UserLtcsSubsidyFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserLtcsSubsidyFinderMixin
{
    /**
     * @var \Domain\User\UserLtcsSubsidyFinder|\Mockery\MockInterface
     */
    protected $userLtcsSubsidyFinder;

    /**
     * UserLtcsSubsidyFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserLtcsSubsidyFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserLtcsSubsidyFinder::class, fn () => $self->userLtcsSubsidyFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userLtcsSubsidyFinder = Mockery::mock(UserLtcsSubsidyFinder::class);
        });
    }
}
