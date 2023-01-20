<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\DeleteUserLtcsSubsidyUseCase;

/**
 * DeleteUserLtcsSubsidyUsecase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DeleteUserLtcsSubsidyUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\User\DeleteUserLtcsSubsidyUseCase */
    protected $deleteUserLtcsSubsidyUseCase;

    /**
     * {@link \UseCase\User\DeleteUserLtcsSubsidyUseCase} に関する初期化。終了処理を登録する
     *
     * @return void
     */
    public static function mixinDeleteUserLtcsSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DeleteUserLtcsSubsidyUseCase::class, fn () => $self->deleteUserLtcsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->deleteUserLtcsSubsidyUseCase = Mockery::mock(DeleteUserLtcsSubsidyUseCase::class);
        });
    }
}
