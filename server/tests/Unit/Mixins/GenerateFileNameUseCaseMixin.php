<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\File\GenerateFileNameUseCase;

/**
 * {@link \UseCase\File\GenerateFileNameUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GenerateFileNameUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\File\GenerateFileNameUseCase
     */
    protected $generateFileNameUseCase;

    /**
     * {@link \UseCase\File\GenerateFileNameUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGenerateFileNameUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GenerateFileNameUseCase::class,
                fn () => $self->generateFileNameUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->generateFileNameUseCase = Mockery::mock(
                GenerateFileNameUseCase::class
            );
        });
    }
}
