<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\SendThirdCallingUseCase;

/**
 * {@link \UseCase\Calling\SendThirdCallingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SendThirdCallingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\SendThirdCallingUseCase
     */
    protected $sendThirdCallingUseCase;

    /**
     * SendThirdCallingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSendThirdCallingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(SendThirdCallingUseCase::class, fn () => $self->sendThirdCallingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->sendThirdCallingUseCase = Mockery::mock(SendThirdCallingUseCase::class);
        });
    }
}
