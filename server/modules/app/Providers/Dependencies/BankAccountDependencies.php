<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\BankAccount\BankAccountRepository;
use Infrastructure\BankAccount\BankAccountRepositoryEloquentImpl;
use UseCase\BankAccount\EditStaffBankAccountInteractor;
use UseCase\BankAccount\EditStaffBankAccountUseCase;
use UseCase\BankAccount\EditUserBankAccountInteractor;
use UseCase\BankAccount\EditUserBankAccountUseCase;
use UseCase\BankAccount\LookupBankAccountInteractor;
use UseCase\BankAccount\LookupBankAccountUseCase;

/**
 * BankAccount Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class BankAccountDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            BankAccountRepository::class => BankAccountRepositoryEloquentImpl::class,
            EditStaffBankAccountUseCase::class => EditStaffBankAccountInteractor::class,
            EditUserBankAccountUseCase::class => EditUserBankAccountInteractor::class,
            LookupBankAccountUseCase::class => LookupBankAccountInteractor::class,
        ];
    }
}
