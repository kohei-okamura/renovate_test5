<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\SendFourthCallingUseCase;

/**
 * {@link \UseCase\Calling\SendFourthCallingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait SendFourthCallingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\SendFourthCallingUseCase
     */
    protected $sendFourthCallingUseCase;

    /**
     * SendFourthCallingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinSendFourthCallingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(SendFourthCallingUseCase::class, fn () => $self->sendFourthCallingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->sendFourthCallingUseCase = Mockery::mock(SendFourthCallingUseCase::class);
        });
    }
}
