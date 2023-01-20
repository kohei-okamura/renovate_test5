<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\File\UploadStorageUseCase;

/**
 * UploadUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UploadUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\File\UploadStorageUseCase
     */
    protected $uploadUseCase;

    /**
     * UploadUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUploadUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UploadStorageUseCase::class, fn () => $self->uploadUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->uploadUseCase = Mockery::mock(UploadStorageUseCase::class);
        });
    }
}
