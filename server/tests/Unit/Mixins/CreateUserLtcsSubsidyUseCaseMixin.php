<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\CreateUserLtcsSubsidyUseCase;

/**
 * CreateUserLtcsSubsidyUsecase Mixin
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateUserLtcsSubsidyUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\User\CreateUserLtcsSubsidyUseCase */
    protected $createUserLtcsSubsidyUseCase;

    /**
     * {@link \UseCase\User\CreateUserLtcsSubsidyUseCase} に関する初期化。終了処理を登録する
     *
     * @return void
     */
    public static function mixinCreateUserLtcsSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateUserLtcsSubsidyUseCase::class, fn () => $self->createUserLtcsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createUserLtcsSubsidyUseCase = Mockery::mock(CreateUserLtcsSubsidyUseCase::class);
        });
    }
}
