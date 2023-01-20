<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\LtcsInsCard\FindLtcsInsCardUseCase;

/**
 * FindLtcsInsCard Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindLtcsInsCardUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\LtcsInsCard\FindLtcsInsCardUseCase
     */
    protected $findLtcsInsCardUseCase;

    /**
     * FindLtcsInsCardUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindLtcsInsCardUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindLtcsInsCardUseCase::class, fn () => $self->findLtcsInsCardUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findLtcsInsCardUseCase = Mockery::mock(FindLtcsInsCardUseCase::class);
        });
    }
}
