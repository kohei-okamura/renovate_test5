<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Calling\CallingResponseRepository;
use Mockery;

/**
 * CallingResponseRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CallingResponseRepositoryMixin
{
    /**
     * @var \Domain\Calling\CallingResponseRepository|\Mockery\MockInterface
     */
    protected $callingResponseRepository;

    /**
     * CallingResponseRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCallingResponseRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CallingResponseRepository::class, fn () => $self->callingResponseRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->callingResponseRepository = Mockery::mock(CallingResponseRepository::class);
        });
    }
}
