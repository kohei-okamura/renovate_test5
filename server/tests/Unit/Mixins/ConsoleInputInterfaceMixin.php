<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use Symfony\Component\Console\Input\InputInterface;

/**
 * {@link \Symfony\Component\Console\Input\InputInterface} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ConsoleInputInterfaceMixin
{
    /**
     * @var \Mockery\MockInterface|\Symfony\Component\Console\Input\InputInterface
     */
    protected $inputInterface;

    /**
     * InputInterface に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConsoleInputInterface(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(InputInterface::class, fn () => $self->inputIntarface);
        });
        static::beforeEachSpec(function ($self): void {
            $self->inputInterface = Mockery::mock(InputInterface::class);
        });
    }
}
