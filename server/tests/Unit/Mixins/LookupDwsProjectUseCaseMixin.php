<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\LookupDwsProjectUseCase;

/**
 * lookupDwsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupDwsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\LookupDwsProjectUseCase
     */
    protected $lookupDwsProjectUseCase;

    /**
     * LookupDwsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupDwsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupDwsProjectUseCase::class, fn () => $self->lookupDwsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupDwsProjectUseCase = Mockery::mock(LookupDwsProjectUseCase::class);
        });
    }
}
