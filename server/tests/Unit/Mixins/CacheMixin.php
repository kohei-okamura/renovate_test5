<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\Cache\CacheManager;
use Mockery;

/**
 * Cache Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CacheMixin
{
    /**
     * @var \Illuminate\Cache\CacheManager|\Mockery\MockInterface
     */
    protected $cache;

    /**
     * Cache に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCache(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind('cache', fn () => $self->cache);
        });
        static::beforeEachSpec(function ($self): void {
            $self->cache = Mockery::mock(CacheManager::class);
        });
    }
}
