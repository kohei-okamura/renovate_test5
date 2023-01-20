<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use App\Http\Requests\Request;
use Mockery;

/**
 * Request Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 * @mixin \Tests\Unit\Mixins\ContextMixin
 */
trait RequestMixin
{
    /**
     * @var \App\Http\Requests\Request|\Mockery\MockInterface
     */
    protected $request;

    /**
     * Request に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRequest(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(Request::class, fn (): Request => $self->request);
        });
        static::beforeEachSpec(function ($self): void {
            $self->request = Mockery::mock(Request::class)->makePartial();
            $self->request->allows('context')->andReturn($self->context);
        });
    }
}
