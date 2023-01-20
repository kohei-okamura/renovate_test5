<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\LookupStaffUseCase;

/**
 * LookupStaffUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupStaffUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\LookupStaffUseCase
     */
    protected $lookupStaffUseCase;

    /**
     * LookupStaffUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupStaffUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupStaffUseCase::class, fn () => $self->lookupStaffUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupStaffUseCase = Mockery::mock(LookupStaffUseCase::class);
        });
    }
}
