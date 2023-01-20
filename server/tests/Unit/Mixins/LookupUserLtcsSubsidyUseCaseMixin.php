<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\LookupUserLtcsSubsidyUseCase;

/**
 * LookupUserLtcsSubsidyUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupUserLtcsSubsidyUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\LookupUserLtcsSubsidyUseCase
     */
    protected $lookupUserLtcsSubsidyUseCase;

    /**
     * {@link \UseCase\User\LookupUserLtcsSubsidyUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupUserLtcsSubsidy(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupUserLtcsSubsidyUseCase::class, fn () => $self->lookupUserLtcsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupUserLtcsSubsidyUseCase = Mockery::mock(LookupUserLtcsSubsidyUseCase::class);
        });
    }
}
