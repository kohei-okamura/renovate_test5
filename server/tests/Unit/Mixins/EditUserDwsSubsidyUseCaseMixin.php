<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\EditUserDwsSubsidyUseCase;

/**
 * EditSubsidyUsecase Mixin
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditUserDwsSubsidyUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\User\EditUserDwsSubsidyUseCase */
    protected $editUserDwsSubsidyUseCase;

    /**
     * {@link \UseCase\User\EditUserDwsSubsidyUseCase} に関する初期化・終了処理を登録する
     *
     * @return void
     */
    public static function mixinEditSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditUserDwsSubsidyUseCase::class, fn () => $self->editUserDwsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editUserDwsSubsidyUseCase = Mockery::mock(EditUserDwsSubsidyUseCase::class);
        });
    }
}
