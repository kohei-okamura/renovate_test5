<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserRepository;
use Mockery;

/**
 * UserRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserRepositoryMixin
{
    /**
     * @var \Domain\User\UserRepository|\Mockery\MockInterface
     */
    protected $userRepository;

    /**
     * UserRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserRepository::class, fn () => $self->userRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userRepository = Mockery::mock(UserRepository::class);
        });
    }
}
