<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\LookupUserUseCase;

/**
 * {@link \UseCase\User\LookupUserUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupUserUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\LookupUserUseCase
     */
    protected $lookupUserUseCase;

    /**
     * {@link \UseCase\User\LookupUserUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupUserUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupUserUseCase::class,
                fn () => $self->lookupUserUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupUserUseCase = Mockery::mock(
                LookupUserUseCase::class
            );
        });
    }
}
