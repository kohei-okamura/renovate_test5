<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountRepository;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Domain\User\UserRepository;
use Lib\Logging;

/**
 * 利用者登録実装.
 */
final class CreateUserInteractor implements CreateUserUseCase
{
    use Logging;

    private UserRepository $userRepository;
    private BankAccountRepository $bankAccountRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\BankAccount\BankAccountRepository $bankAccountRepository
     * @param \Domain\User\UserRepository $userRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        BankAccountRepository $bankAccountRepository,
        UserRepository $userRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->bankAccountRepository = $bankAccountRepository;
        $this->userRepository = $userRepository;
        $this->transaction = $transactionManagerFactory->factory($userRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, User $user, callable $f): void
    {
        $storedEntity = $this->transaction->run(function () use ($context, $user, $f): User {
            $bankAccount = $this->bankAccountRepository->store($this->bankAccountEntity());
            $entity = $this->userRepository->store($user->copy([
                'organizationId' => $context->organization->id,
                'bankAccountId' => $bankAccount->id,
            ]));
            $f($entity);
            return $entity;
        });

        $this->logger()->info(
            '利用者が登録されました',
            ['id' => $storedEntity->id] + $context->logContext()
        );
    }

    /**
     * 初回登録用の銀行口座のエンティティを作成する.
     *
     * @return \Domain\BankAccount\BankAccount
     */
    private function bankAccountEntity(): BankAccount
    {
        return BankAccount::create([
            'bankName' => ' ',
            'bankCode' => ' ',
            'bankBranchName' => ' ',
            'bankBranchCode' => ' ',
            'bankAccountType' => BankAccountType::unknown(),
            'bankAccountNumber' => ' ',
            'bankAccountHolder' => ' ',
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }
}
