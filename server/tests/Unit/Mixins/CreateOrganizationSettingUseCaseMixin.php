<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Organization\CreateOrganizationSettingUseCase;

/**
 * {@link \UseCase\Organization\CreateOrganizationSettingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateOrganizationSettingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Organization\CreateOrganizationSettingUseCase
     */
    protected $createOrganizationSettingUseCase;

    /**
     * {@link \UseCase\Organization\CreateOrganizationSettingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateOrganizationSettingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateOrganizationSettingUseCase::class,
                fn () => $self->createOrganizationSettingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createOrganizationSettingUseCase = Mockery::mock(
                CreateOrganizationSettingUseCase::class
            );
        });
    }
}
