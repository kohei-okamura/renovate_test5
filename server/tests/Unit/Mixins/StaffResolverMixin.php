<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use App\Resolvers\StaffResolver;
use Mockery;
use ScalikePHP\Option;

/**
 * StaffResolver Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait StaffResolverMixin
{
    /**
     * @var \App\Resolvers\StaffResolver|\Mockery\MockInterface
     */
    protected $staffResolver;

    /**
     * StaffResolver に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStaffResolver(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StaffResolver::class, fn () => $self->staffResolver);
        });
        static::beforeEachSpec(function ($self): void {
            $self->staffResolver = Mockery::mock(StaffResolver::class);
            $self->staffResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->staffs[0]))
                ->byDefault();
        });
    }
}
