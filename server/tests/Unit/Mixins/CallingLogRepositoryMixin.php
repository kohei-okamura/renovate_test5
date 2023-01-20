<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Calling\CallingLogRepository;
use Mockery;

/**
 * CallingLogRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CallingLogRepositoryMixin
{
    /**
     * @var \Domain\Calling\CallingLogRepository|\Mockery\MockInterface
     */
    protected $callingLogRepository;

    /**
     * CallingLogRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCallingLogRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CallingLogRepository::class, fn () => $self->callingLogRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->callingLogRepository = Mockery::mock(CallingLogRepository::class);
        });
    }
}
