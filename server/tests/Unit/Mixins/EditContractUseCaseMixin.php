<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Contract\EditContractUseCase;

/**
 * EditContractUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditContractUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Contract\EditContractUseCase
     */
    protected $editContractUseCase;

    /**
     * EditContractUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditContractUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditContractUseCase::class, fn () => $self->editContractUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editContractUseCase = Mockery::mock(EditContractUseCase::class);
        });
    }
}
