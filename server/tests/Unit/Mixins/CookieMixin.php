<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\Cookie\CookieJar;
use Mockery;

/**
 * Cookie Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CookieMixin
{
    /**
     * @var \Mockery\MockInterface|\Symfony\Component\HttpFoundation\Cookie
     */
    protected $cookie;

    /**
     * Cookie に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinCookie(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind('cookie', fn () => $self->cookie);
        });
        static::beforeEachSpec(function ($self): void {
            $self->cookie = Mockery::mock(CookieJar::class);
        });
    }
}
