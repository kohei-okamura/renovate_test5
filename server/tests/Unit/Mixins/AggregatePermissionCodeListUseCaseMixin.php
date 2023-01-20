<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\AggregatePermissionCodeListUseCase;

/**
 * {@link \UseCase\Staff\AggregatePermissionCodeListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait AggregatePermissionCodeListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\AggregatePermissionCodeListUseCase
     */
    protected $aggregatePermissionCodeListUseCase;

    /**
     * AggregatePermissionCodeListUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinAggregatePermissionCodeListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                AggregatePermissionCodeListUseCase::class,
                fn () => $self->aggregatePermissionCodeListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->aggregatePermissionCodeListUseCase = Mockery::mock(
                AggregatePermissionCodeListUseCase::class
            );
        });
    }
}
