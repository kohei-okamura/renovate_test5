<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\BankAccount\EditUserBankAccountUseCase;

/**
 * EditUserBankAccountUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditUserBankAccountUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\BankAccount\EditUserBankAccountUseCase
     */
    protected $editUserBankAccountUseCase;

    /**
     * EditUserBankAccountUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditUserBankAccountUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditUserBankAccountUseCase::class, fn () => $self->editUserBankAccountUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editUserBankAccountUseCase = Mockery::mock(EditUserBankAccountUseCase::class);
        });
    }
}
