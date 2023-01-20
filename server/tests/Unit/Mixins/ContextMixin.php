<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Context\Context;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\App\Http\Concretes\TestingContext;

/**
 * Context Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait ContextMixin
{
    /**
     * @var \Mockery\MockInterface|\Tests\Unit\App\Http\Concretes\TestingContext
     */
    protected $context;

    /**
     * Context に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinContext(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(Context::class, fn () => $self->context);
        });
        static::beforeEachSpec(function ($self): void {
            $self->context = Mockery::mock(TestingContext::class)->makePartial();
            assert($self->context instanceof TestingContext);
            TestingContext::prepare(
                $self->context,
                $self->examples->organizations[0],
                Option::from($self->examples->staffs[0]),
                true
            );
        });
    }
}
