<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Organization\EditOrganizationSettingUseCase;

/**
 * {@link \UseCase\Organization\EditOrganizationSettingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditOrganizationSettingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Organization\EditOrganizationSettingUseCase
     */
    protected $editOrganizationSettingUseCase;

    /**
     * {@link \UseCase\Organization\EditOrganizationSettingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditOrganizationSettingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditOrganizationSettingUseCase::class,
                fn () => $self->editOrganizationSettingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editOrganizationSettingUseCase = Mockery::mock(
                EditOrganizationSettingUseCase::class
            );
        });
    }
}
