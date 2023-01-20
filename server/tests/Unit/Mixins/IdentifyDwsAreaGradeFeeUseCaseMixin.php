<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase;

/**
 * {@link \UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyDwsAreaGradeFeeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase
     */
    protected $identifyDwsAreaGradeFeeUseCase;

    /**
     * {@link \UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyDwsAreaGradeFeeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyDwsAreaGradeFeeUseCase::class,
                fn () => $self->identifyDwsAreaGradeFeeUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyDwsAreaGradeFeeUseCase = Mockery::mock(
                IdentifyDwsAreaGradeFeeUseCase::class
            );
        });
    }
}
