<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\LookupLtcsProjectServiceMenuUseCase;

/**
 * LookupLtcsProjectServiceMenuUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupLtcsProjectServiceMenuUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\LookupLtcsProjectServiceMenuUseCase
     */
    protected $lookupLtcsProjectServiceMenuUseCase;

    /**
     * LookupLtcsProjectServiceMenuUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupLtcsProjectServiceMenuUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupLtcsProjectServiceMenuUseCase::class, fn () => $self->lookupLtcsProjectServiceMenuUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupLtcsProjectServiceMenuUseCase = Mockery::mock(LookupLtcsProjectServiceMenuUseCase::class);
        });
    }
}
