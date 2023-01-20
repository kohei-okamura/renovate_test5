<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Context\Context;
use Mockery;

/**
 * {@link \Domain\Context\Context} Mixin without {@link \Tests\Unit\Examples\ExamplesConsumer}.
 *
 * - Context のプロパティを参照しない場合に余分な処理を行わず高速にテストを実行できる.
 * - 上記以外のケースにおいては正常に動作しないため {@link \Tests\Unit\Mixins\ContextMixin} を用いること.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DummyContextMixin
{
    /**
     * @var \Domain\Context\Context|\Mockery\MockInterface
     */
    protected $context;

    /**
     * {@link \Domain\Context\Context} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinContext(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(Context::class, fn () => $self->context);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->context = Mockery::mock(Context::class);
        });
    }
}
