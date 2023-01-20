<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\FindUserLtcsSubsidyUseCase;

/**
 * {@link \UseCase\User\FindUserLtcsSubsidyUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindUserLtcsSubsidyUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\FindUserLtcsSubsidyUseCase
     */
    protected $findUserLtcsSubsidyUseCase;

    /**
     * {@link \UseCase\User\FindUserLtcsSubsidyUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindUserLtcsSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                FindUserLtcsSubsidyUseCase::class,
                fn () => $self->findUserLtcsSubsidyUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->findUserLtcsSubsidyUseCase = Mockery::mock(
                FindUserLtcsSubsidyUseCase::class
            );
        });
    }
}
