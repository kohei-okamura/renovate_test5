<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\IdentifyUserDwsSubsidyUseCase;

/**
 * {@link \UseCase\User\IdentifyUserDwsSubsidyUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyUserDwsSubsidyUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\IdentifyUserDwsSubsidyUseCase
     */
    protected $identifyUserDwsSubsidyUseCase;

    /**
     * {@link \UseCase\User\IdentifyUserDwsSubsidyUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyUserDwsSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyUserDwsSubsidyUseCase::class,
                fn () => $self->identifyUserDwsSubsidyUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyUserDwsSubsidyUseCase = Mockery::mock(
                IdentifyUserDwsSubsidyUseCase::class
            );
        });
    }
}
