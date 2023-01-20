<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Config\Config;
use Mockery;

/**
 * Config Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ConfigMixin
{
    /**
     * @var \Domain\Config\Config|\Mockery\MockInterface
     */
    protected $config;

    /**
     * {@link \Domain\Config\Config} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinConfig(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(Config::class, fn () => $self->config);
        });
        static::beforeEachSpec(function ($self): void {
            $self->config = Mockery::mock(Config::class);
            $self->config->allows('get')->with('zinger.uri.scheme')->andReturn('https');
            $self->config->allows('get')->with('zinger.uri.app_domain')->andReturn('zinger.test');
            $self->config->allows('get')->with('zinger.uri.base_path')->andReturn('api');
        });
    }
}
