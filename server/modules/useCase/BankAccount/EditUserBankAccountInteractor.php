<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
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
use UseCase\User\LookupUserUseCase;

/**
 * 利用者銀行口座編集実装.
 */
final class EditUserBankAccountInteractor implements EditUserBankAccountUseCase
{
    use Logging;

    private LookupUserUseCase $lookupUserUseCase;
    private LookupBankAccountUseCase $lookupBankAccountUseCase;
    private BankAccountRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\BankAccount\LookupBankAccountUseCase $lookupBankAccountUseCase
     * @param \Domain\BankAccount\BankAccountRepository $repository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserUseCase $lookupUserUseCase,
        LookupBankAccountUseCase $lookupBankAccountUseCase,
        BankAccountRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUserUseCase = $lookupUserUseCase;
        $this->lookupBankAccountUseCase = $lookupBankAccountUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, array $values): BankAccount
    {
        /** @var \Domain\User\User $user */
        $user = $this->lookupUserUseCase
            ->handle($context, Permission::updateUsers(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId) {
                throw new NotFoundException("User[{$userId}] is not found");
            });

        $bankAccount = $this->lookupBankAccountUseCase
            ->handle($context, $user->bankAccountId)
            ->headOption()
            ->getOrElse(function () use ($user) {
                throw new NotFoundException("BankAccount({$user->bankAccountId}) not found");
            });

        $x = $this->transaction->run(fn (): BankAccount => $this->repository->store(
            $bankAccount->copy(
                $values + [
                    'updatedAt' => Carbon::now(),
                    'version' => $bankAccount->version + 1,
                ]
            )
        ));
        $this->logger()->info(
            '利用者の銀行口座が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
