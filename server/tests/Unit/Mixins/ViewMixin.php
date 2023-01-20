<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Illuminate\View\Factory;
use Mockery;

/**
 * {@link \Illuminate\View\Factory} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ViewMixin
{
    /**
     * @var \Illuminate\View\Factory|\Mockery\MockInterface
     */
    protected $view;

    /**
     * View に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinView(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind('view', fn () => $self->view);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->view = Mockery::mock(Factory::class);
        });
    }
}
