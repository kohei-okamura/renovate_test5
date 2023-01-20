<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Organization\GetAllValidOrganizationUseCase;

/**
 * {@link \UseCase\Organization\GetAllValidOrganizationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetAllValidOrganizationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Organization\GetAllValidOrganizationUseCase
     */
    protected $getAllValidOrganizationUseCase;

    /**
     * GetAllValidOrganizationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetAllValidOrganization(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetAllValidOrganizationUseCase::class, fn () => $self->getAllValidOrganizationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getAllValidOrganizationUseCase = Mockery::mock(GetAllValidOrganizationUseCase::class);
        });
    }
}
