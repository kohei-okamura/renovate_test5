<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\RemoveStaffRememberTokenUseCase;

/**
 * RemoveStaffRememberTokenUseCase Mixin
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RemoveStaffRememberTokenUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\RemoveStaffRememberTokenUseCase
     */
    protected $removeStaffRememberTokenUseCase;

    public static function mixinRemoveStaffRememberTokenUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RemoveStaffRememberTokenUseCase::class, fn () => $self->removeStaffRememberTokenUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->removeStaffRememberTokenUseCase = Mockery::mock(RemoveStaffRememberTokenUseCase::class);
        });
    }
}
