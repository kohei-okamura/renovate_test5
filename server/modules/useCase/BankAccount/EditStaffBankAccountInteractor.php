<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\BankAccount;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Staff\LookupStaffUseCase;

/**
 * スタッフの銀行口座編集実装。
 */
final class EditStaffBankAccountInteractor implements EditStaffBankAccountUseCase
{
    use Logging;

    private LookupStaffUseCase $lookupStaffUseCase;
    private LookupBankAccountUseCase $lookupBankAccountUseCase;
    private BankAccountRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     * @param \UseCase\BankAccount\LookupBankAccountUseCase $lookupBankAccountUseCase
     * @param \Domain\BankAccount\BankAccountRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupStaffUseCase $lookupStaffUseCase,
        LookupBankAccountUseCase $lookupBankAccountUseCase,
        BankAccountRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->lookupBankAccountUseCase = $lookupBankAccountUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    public function handle(Context $context, int $staffId, array $value): BankAccount
    {
        /** @var \Domain\Staff\Staff $staff */
        $staff = $this->lookupStaffUseCase
            ->handle($context, Permission::updateStaffs(), $staffId)
            ->headOption()
            ->getOrElse(function () use ($staffId) {
                throw new NotFoundException("Staff({$staffId}) not found");
            });

        $bankAccount = $this->lookupBankAccountUseCase
            ->handle($context, $staff->bankAccountId)
            ->headOption()
            ->getOrElse(function () use ($staff) {
                throw new NotFoundException("BankAccount({$staff->bankAccountId}) not found");
            });
        $x = $this->transaction->run(fn (): BankAccount => $this->repository->store(
            $bankAccount->copy(
                $value + [
                    'updatedAt' => Carbon::now(),
                    'version' => $bankAccount->version + 1,
                ]
            )
        ));
        $this->logger()->info(
            'スタッフの銀行口座が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
