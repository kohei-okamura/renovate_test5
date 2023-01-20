<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Organization\LookupOrganizationSettingUseCase;

/**
 * LookupOrganizationSettingUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupOrganizationSettingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Organization\LookupOrganizationSettingUseCase
     */
    protected $lookupOrganizationSettingUseCase;

    /**
     * LookupOrganizationSettingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupOrganizationSetting(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupOrganizationSettingUseCase::class, fn () => $self->lookupOrganizationSettingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupOrganizationSettingUseCase = Mockery::mock(LookupOrganizationSettingUseCase::class);
        });
    }
}
