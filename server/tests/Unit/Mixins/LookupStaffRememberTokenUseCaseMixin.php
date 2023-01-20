<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\LookupStaffRememberTokenUseCase;

/**
 * LookupStaffRememberTokenUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupStaffRememberTokenUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\LookupStaffRememberTokenUseCase
     */
    protected $lookupStaffRememberTokenUseCase;

    /**
     * LookupStaffRememberTokenUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupStaffRememberTokenUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupStaffRememberTokenUseCase::class, fn () => $self->lookupStaffRememberTokenUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupStaffRememberTokenUseCase = Mockery::mock(LookupStaffRememberTokenUseCase::class);
        });
    }
}
