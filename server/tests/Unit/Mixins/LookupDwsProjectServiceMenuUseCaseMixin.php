<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\LookupDwsProjectServiceMenuUseCase;

/**
 * LookupDwsProjectServiceMenuUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsProjectServiceMenuUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\LookupDwsProjectServiceMenuUseCase
     */
    protected $lookupDwsProjectServiceMenuUseCase;

    /**
     * LookupDwsProjectServiceMenuUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsProjectServiceMenuUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupDwsProjectServiceMenuUseCase::class, fn () => $self->lookupDwsProjectServiceMenuUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupDwsProjectServiceMenuUseCase = Mockery::mock(LookupDwsProjectServiceMenuUseCase::class);
        });
    }
}
