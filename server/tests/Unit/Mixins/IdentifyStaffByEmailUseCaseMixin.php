<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\IdentifyStaffByEmailUseCase;

/**
 * IdentifyStaffByEmailUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyStaffByEmailUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\IdentifyStaffByEmailUseCase
     */
    protected $identifyStaffByEmailUseCase;

    /**
     * IdentifyStaffByEmailUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyStaffByEmailUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(IdentifyStaffByEmailUseCase::class, fn () => $self->identifyStaffByEmailUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->identifyStaffByEmailUseCase = Mockery::mock(IdentifyStaffByEmailUseCase::class);
        });
    }
}
