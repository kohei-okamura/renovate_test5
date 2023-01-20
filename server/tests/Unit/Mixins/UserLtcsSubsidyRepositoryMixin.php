<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserLtcsSubsidyRepository;
use Mockery;

/**
 * UserLtcsSubsidyRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserLtcsSubsidyRepositoryMixin
{
    /** @var \Domain\User\UserLtcsSubsidyRepository|\Mockery\MockInterface */
    protected $userLtcsSubsidyRepository;

    /**
     * {@link \Domain\User\UserLtcsSubsidyRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinUserLtcsSubsidyRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserLtcsSubsidyRepository::class, fn () => $self->userLtcsSubsidyRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userLtcsSubsidyRepository = Mockery::mock(UserLtcsSubsidyRepository::class);
        });
    }
}
