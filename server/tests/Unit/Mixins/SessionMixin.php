<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\Contracts\Session\Session;
use Mockery;

/**
 * Session Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SessionMixin
{
    /**
     * @var \Illuminate\Contracts\Session\Session|\Mockery\MockInterface
     */
    protected $session;

    /**
     * Session に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSession(): void
    {
        static::beforeEachSpec(function ($self): void {
            $self->session = Mockery::mock(Session::class);
        });
    }
}
