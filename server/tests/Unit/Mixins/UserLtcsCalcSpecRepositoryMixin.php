<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserLtcsCalcSpecRepository;
use Mockery;

/**
 * {@link \Domain\User\UserLtcsCalcSpecRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserLtcsCalcSpecRepositoryMixin
{
    /**
     * @var \Domain\User\UserLtcsCalcSpecRepository|\Mockery\MockInterface
     */
    protected $userLtcsCalcSpecRepository;

    /**
     * {@link \Domain\User\UserLtcsCalcSpecRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserLtcsCalcSpecRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UserLtcsCalcSpecRepository::class,
                fn () => $self->userLtcsCalcSpecRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->userLtcsCalcSpecRepository = Mockery::mock(
                UserLtcsCalcSpecRepository::class
            );
        });
    }
}
