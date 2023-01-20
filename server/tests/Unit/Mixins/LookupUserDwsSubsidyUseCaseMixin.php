<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\LookupUserDwsSubsidyUseCase;

/**
 * LookupUserDwsSubsidyUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupUserDwsSubsidyUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\LookupUserDwsSubsidyUseCase
     */
    protected $lookupUserDwsSubsidyUseCase;

    /**
     * {@link \UseCase\User\LookupUserDwsSubsidyUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupUserDwsSubsidy(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupUserDwsSubsidyUseCase::class, fn () => $self->lookupUserDwsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupUserDwsSubsidyUseCase = Mockery::mock(LookupUserDwsSubsidyUseCase::class);
        });
    }
}
