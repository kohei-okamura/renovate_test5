<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserDwsSubsidyRepository;
use Mockery;

/**
 * UserDwsSubsidyRepositoryMixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserDwsSubsidyRepositoryMixin
{
    /** @var \Domain\User\UserDwsSubsidyRepository|\Mockery\MockInterface */
    protected $userDwsSubsidyRepository;

    /**
     * {@link \Domain\User\UserSubsidyRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinSubsidyRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserDwsSubsidyRepository::class, fn () => $self->userDwsSubsidyRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userDwsSubsidyRepository = Mockery::mock(UserDwsSubsidyRepository::class);
        });
    }
}
