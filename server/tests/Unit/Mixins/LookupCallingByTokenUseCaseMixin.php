<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\LookupCallingByTokenUseCase;
use UseCase\Calling\LookupCallingUseCase;

/**
 * {@link \UseCase\Calling\LookupCallingByTokenUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupCallingByTokenUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\LookupCallingByTokenUseCase
     */
    protected $lookupCallingByTokenUseCase;

    /**
     * LookupCallingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupCalling(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupCallingByTokenUseCase::class, fn () => $self->lookupCallingByTokenUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupCallingByTokenUseCase = Mockery::mock(LookupCallingByTokenUseCase::class);
        });
    }
}
