<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\File\GenerateFileNameContainsUserNameUseCase;

/**
 * {@link \UseCase\File\GenerateFileNameContainsUserNameUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GenerateFileNameContainsUserNameUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\File\GenerateFileNameContainsUserNameUseCase
     */
    protected $generateFileNameContainsUserNameUseCase;

    /**
     * {@link \UseCase\File\GenerateFileNameContainsUserNameUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGenerateFileNameContainsUserNameUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GenerateFileNameContainsUserNameUseCase::class,
                fn () => $self->generateFileNameContainsUserNameUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->generateFileNameContainsUserNameUseCase = Mockery::mock(
                GenerateFileNameContainsUserNameUseCase::class
            );
        });
    }
}
