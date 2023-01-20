<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\LookupLtcsProjectUseCase;

/**
 * LookupLtcsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupLtcsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\LookupLtcsProjectUseCase
     */
    protected $lookupLtcsProjectUseCase;

    /**
     * LookupLtcsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupLtcsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupLtcsProjectUseCase::class, fn () => $self->lookupLtcsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupLtcsProjectUseCase = Mockery::mock(LookupLtcsProjectUseCase::class);
        });
    }
}
