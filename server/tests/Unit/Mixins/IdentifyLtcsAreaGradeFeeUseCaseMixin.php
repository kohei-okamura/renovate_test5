<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase;

/**
 * {@link \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyLtcsAreaGradeFeeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase
     */
    protected $identifyLtcsAreaGradeFeeUseCase;

    /**
     * {@link \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyLtcsAreaGradeFeeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyLtcsAreaGradeFeeUseCase::class,
                fn () => $self->identifyLtcsAreaGradeFeeUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyLtcsAreaGradeFeeUseCase = Mockery::mock(
                IdentifyLtcsAreaGradeFeeUseCase::class
            );
        });
    }
}
