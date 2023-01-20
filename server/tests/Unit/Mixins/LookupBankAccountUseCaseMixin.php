<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\BankAccount\LookupBankAccountUseCase;

/**
 * LookupBankAccountUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupBankAccountUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\BankAccount\LookupBankAccountUseCase
     */
    protected $lookupBankAccountUseCase;

    /**
     * LookupBankAccountUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupBankAccount(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupBankAccountUseCase::class, fn () => $self->lookupBankAccountUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupBankAccountUseCase = Mockery::mock(LookupBankAccountUseCase::class);
        });
    }
}
