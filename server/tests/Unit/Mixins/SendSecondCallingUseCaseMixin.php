<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\SendSecondCallingUseCase;

/**
 * {@link \UseCase\Calling\SendSecondCallingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SendSecondCallingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\SendSecondCallingUseCase
     */
    protected $sendSecondCallingUseCase;

    /**
     * SendSecondCallingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSendSecondCallingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(SendSecondCallingUseCase::class, fn () => $self->sendSecondCallingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->sendSecondCallingUseCase = Mockery::mock(SendSecondCallingUseCase::class);
        });
    }
}
