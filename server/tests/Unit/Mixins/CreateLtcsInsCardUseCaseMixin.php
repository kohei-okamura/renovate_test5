<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\LtcsInsCard\CreateLtcsInsCardUseCase;

/**
 * CreateLtcsInsCardUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsInsCardUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\LtcsInsCard\CreateLtcsInsCardUseCase
     */
    protected $createLtcsInsCardUseCase;

    /**
     * CreateLtcsInsCardUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsInsCardUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateLtcsInsCardUseCase::class, fn () => $self->createLtcsInsCardUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createLtcsInsCardUseCase = Mockery::mock(CreateLtcsInsCardUseCase::class);
        });
    }
}
