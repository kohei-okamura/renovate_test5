<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserDwsCalcSpecRepository;
use Mockery;

/**
 * {@link \Domain\User\UserDwsCalcSpecRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserDwsCalcSpecRepositoryMixin
{
    /**
     * @var \Domain\User\UserDwsCalcSpecRepository|\Mockery\MockInterface
     */
    protected $userDwsCalcSpecRepository;

    /**
     * {@link \Domain\User\UserDwsCalcSpecRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserDwsCalcSpecRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UserDwsCalcSpecRepository::class,
                fn () => $self->userDwsCalcSpecRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->userDwsCalcSpecRepository = Mockery::mock(
                UserDwsCalcSpecRepository::class
            );
        });
    }
}
