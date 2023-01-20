<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\GetSessionInfoUseCase;

/**
 * GetSessionInfoUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetSessionInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\GetSessionInfoUseCase
     */
    protected $getSessionInfoUseCase;

    /**
     * GetSessionInfoUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetSessionInfo(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetSessionInfoUseCase::class, fn () => $self->getSessionInfoUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getSessionInfoUseCase = Mockery::mock(GetSessionInfoUseCase::class);
        });
    }
}
