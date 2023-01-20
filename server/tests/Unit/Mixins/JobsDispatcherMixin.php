<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherInterface;
use Illuminate\Support\Testing\Fakes\BusFake;

/**
 * Dispatcher Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait JobsDispatcherMixin
{
    protected BusFake $dispatcher;

    /**
     * Dispatcher に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDispatcher(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DispatcherInterface::class, fn () => $self->dispatcher === null ? new Dispatcher(app()) : $self->dispatcher);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dispatcher = new BusFake(new Dispatcher(app()));
        });
    }
}
