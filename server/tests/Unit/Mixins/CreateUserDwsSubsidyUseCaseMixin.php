<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\CreateUserDwsSubsidyUseCase;

/**
 * CreateUserDwsSubsidyUsecase Mixin
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateUserDwsSubsidyUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\User\CreateUserDwsSubsidyUseCase */
    protected $createUserDwsSubsidyUseCase;

    /**
     * {@link \UseCase\User\CreateUserDwsSubsidyUseCase} に関する初期化。終了処理を登録する
     *
     * @return void
     */
    public static function mixinCreateUserDwsSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateUserDwsSubsidyUseCase::class, fn () => $self->createUserDwsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createUserDwsSubsidyUseCase = Mockery::mock(CreateUserDwsSubsidyUseCase::class);
        });
    }
}
