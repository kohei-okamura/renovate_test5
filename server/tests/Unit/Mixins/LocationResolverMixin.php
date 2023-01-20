<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Common\LocationResolver;
use Mockery;

/**
 * LocationResolver Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LocationResolverMixin
{
    /**
     * @var \Domain\Common\LocationResolver|\Mockery\MockInterface
     */
    protected $resolver;

    /**
     * LocationResolver に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLocationResolver(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LocationResolver::class, fn () => $self->resolver);
        });
        static::beforeEachSpec(function ($self): void {
            $self->resolver = Mockery::mock(LocationResolver::class);
        });
    }
}
