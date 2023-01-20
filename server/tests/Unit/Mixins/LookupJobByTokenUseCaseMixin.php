<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\LookupJobByTokenUseCase;

/**
 * LookupJobByTokenUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupJobByTokenUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Job\LookupJobByTokenUseCase
     */
    protected $lookupJobByTokenUseCase;

    /**
     * LookupJobByTokenUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupJobByTokenUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupJobByTokenUseCase::class, fn () => $self->lookupJobByTokenUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupJobByTokenUseCase = Mockery::mock(LookupJobByTokenUseCase::class);
        });
    }
}
