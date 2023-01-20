<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\CreateStaffRememberTokenUseCase;

/**
 * CreateStaffRememberTokenUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateStaffRememberTokenUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\CreateStaffRememberTokenUseCase
     */
    protected $createRememberTokenUseCase;

    /**
     * CreateStaffRememberTokenUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinCreateStaffRememberTokenUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateStaffRememberTokenUseCase::class, fn () => $self->createRememberTokenUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createRememberTokenUseCase = Mockery::mock(CreateStaffRememberTokenUseCase::class);
        });
    }
}
