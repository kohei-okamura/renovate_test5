<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\LookupJobUseCase;

/**
 * LookupJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Job\LookupJobUseCase
     */
    protected $lookupJobUseCase;

    /**
     * LookupJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupJobUseCase::class, fn () => $self->lookupJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupJobUseCase = Mockery::mock(LookupJobUseCase::class);
        });
    }
}
