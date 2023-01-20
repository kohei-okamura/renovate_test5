<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsHomeHelpServiceChunkListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase
     */
    protected $createDwsHomeHelpServiceChunkListUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsHomeHelpServiceChunkListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsHomeHelpServiceChunkListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsHomeHelpServiceChunkListUseCase::class,
                fn () => $self->createDwsHomeHelpServiceChunkListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsHomeHelpServiceChunkListUseCase = Mockery::mock(
                CreateDwsHomeHelpServiceChunkListUseCase::class
            );
        });
    }
}
