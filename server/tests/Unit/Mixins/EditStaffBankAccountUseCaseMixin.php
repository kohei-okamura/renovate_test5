<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\BankAccount\EditStaffBankAccountUseCase;

/**
 * EditStaffBankAccountUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditStaffBankAccountUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\BankAccount\EditStaffBankAccountUseCase
     */
    protected $editStaffBankAccountUseCase;

    /**
     * EditStaffBankAccountUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditStaffBankAccountUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditStaffBankAccountUseCase::class, fn () => $self->editStaffBankAccountUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editStaffBankAccountUseCase = Mockery::mock(EditStaffBankAccountUseCase::class);
        });
    }
}
