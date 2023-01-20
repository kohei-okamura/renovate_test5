<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\SendFirstCallingUseCase;

/**
 * {@link \UseCase\Calling\SendFirstCallingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SendFirstCallingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\SendFirstCallingUseCase
     */
    protected $sendFirstCallingUseCase;

    /**
     * SendFirstCallingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSendFirstCallingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(SendFirstCallingUseCase::class, fn () => $self->sendFirstCallingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->sendFirstCallingUseCase = Mockery::mock(SendFirstCallingUseCase::class);
        });
    }
}
