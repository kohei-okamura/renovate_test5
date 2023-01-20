<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildLtcsServiceDetailListUseCase;

/**
 * {@link \UseCase\Billing\BuildLtcsServiceDetailListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildLtcsServiceDetailListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildLtcsServiceDetailListUseCase
     */
    protected $buildLtcsServiceDetailListUseCase;

    /**
     * {@link \UseCase\Billing\BuildLtcsServiceDetailListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildLtcsServiceDetailListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildLtcsServiceDetailListUseCase::class,
                fn () => $self->buildLtcsServiceDetailListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildLtcsServiceDetailListUseCase = Mockery::mock(
                BuildLtcsServiceDetailListUseCase::class
            );
        });
    }
}
