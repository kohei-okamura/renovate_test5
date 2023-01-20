<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\LtcsInsCard\EditLtcsInsCardUseCase;

/**
 * EditLtcsInsCardUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditLtcsInsCardUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\LtcsInsCard\EditLtcsInsCardUseCase
     */
    protected $editLtcsInsCardUseCase;

    /**
     * EditLtcsInsCardUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditLtcsInsCardUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditLtcsInsCardUseCase::class, fn () => $self->editLtcsInsCardUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editLtcsInsCardUseCase = Mockery::mock(EditLtcsInsCardUseCase::class);
        });
    }
}
