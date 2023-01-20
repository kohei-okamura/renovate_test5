<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contracts\TokenMaker;

/**
 * TokenMaker Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait TokenMakerMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Contracts\TokenMaker
     */
    protected $tokenMaker;

    /**
     * TokenMaker に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinTokenMaker(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(TokenMaker::class, fn () => $self->tokenMaker);
        });
        static::beforeEachSpec(function ($self): void {
            $self->tokenMaker = Mockery::mock(TokenMaker::class);
        });
    }
}
