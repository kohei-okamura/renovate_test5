<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\LookupOfficeUseCase;

/**
 * LookupOfficeUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupOfficeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\LookupOfficeUseCase
     */
    protected $lookupOfficeUseCase;

    /**
     * LookupOfficeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupOffice(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupOfficeUseCase::class, fn () => $self->lookupOfficeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupOfficeUseCase = Mockery::mock(LookupOfficeUseCase::class);
        });
    }
}
