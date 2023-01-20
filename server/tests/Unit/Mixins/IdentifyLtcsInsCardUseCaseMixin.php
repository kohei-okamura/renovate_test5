<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase;

/**
 * {@link \UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyLtcsInsCardUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase
     */
    protected $identifyLtcsInsCardUseCase;

    /**
     * {@link \UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyLtcsInsCardUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyLtcsInsCardUseCase::class,
                fn () => $self->identifyLtcsInsCardUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyLtcsInsCardUseCase = Mockery::mock(
                IdentifyLtcsInsCardUseCase::class
            );
        });
    }
}
