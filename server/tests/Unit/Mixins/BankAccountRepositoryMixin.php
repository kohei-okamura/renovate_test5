<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\BankAccount\BankAccountRepository;
use Mockery;

/**
 * BankAccountRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BankAccountRepositoryMixin
{
    /**
     * @var \Domain\BankAccount\BankAccountRepository|\Mockery\MockInterface
     */
    protected $bankAccountRepository;

    /**
     * BankAccountRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBankAccountRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(BankAccountRepository::class, fn () => $self->bankAccountRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->bankAccountRepository = Mockery::mock(BankAccountRepository::class);
        });
    }
}
