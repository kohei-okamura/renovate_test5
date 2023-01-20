<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserDwsSubsidyFinder;
use Mockery;

/**
 * UserDwsSubsidyFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserDwsSubsidyFinderMixin
{
    /** @var \Domain\User\UserDwsSubsidyFinder|\Mockery\MockInterface */
    protected $userDwsSubsidyFinder;

    public static function mixinDwsCertificationFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserDwsSubsidyFinder::class, fn () => $self->userDwsSubsidyFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userDwsSubsidyFinder = Mockery::mock(UserDwsSubsidyFinder::class);
        });
    }
}
