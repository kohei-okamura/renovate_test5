<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Laravel\Lumen\Http\Request as LumenRequest;
use Mockery;

/**
 * Lumen Request Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LumenRequestMixin
{
    /**
     * @var \App\Http\Requests\Request|\Mockery\MockInterface
     */
    protected $lumenRequest;

    /**
     * Request に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRequest(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LumenRequest::class, fn () => $self->lumenRequest);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lumenRequest = Mockery::mock(LumenRequest::class);
        });
    }
}
