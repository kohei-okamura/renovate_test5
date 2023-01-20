<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Role\LookupRoleUseCase;

/**
 * LookupRoleUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupRoleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Role\LookupRoleUseCase
     */
    protected $lookupRoleUseCase;

    /**
     * LookupRoleUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupRoleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupRoleUseCase::class, fn () => $self->lookupRoleUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupRoleUseCase = Mockery::mock(LookupRoleUseCase::class);
        });
    }
}
