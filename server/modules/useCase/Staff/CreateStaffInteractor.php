<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountRepository;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Staff\Invitation;
use Domain\Staff\Staff;
use Domain\Staff\StaffRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use ScalikePHP\Option;

/**
 * スタッフ登録ユースケース実装.
 */
final class CreateStaffInteractor implements CreateStaffUseCase
{
    use Logging;

    private BankAccountRepository $bankAccountRepository;
    private EditInvitationUseCase $editInvitationUseCase;
    private StaffRepository $staffRepository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Staff\CreateStaffInteractor} Constructor.
     *
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \Domain\BankAccount\BankAccountRepository $bankAccountRepository
     * @param \UseCase\Staff\EditInvitationUseCase $editInvitationUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        StaffRepository $staffRepository,
        BankAccountRepository $bankAccountRepository,
        EditInvitationUseCase $editInvitationUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->staffRepository = $staffRepository;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->editInvitationUseCase = $editInvitationUseCase;
        $this->transaction = $transactionManagerFactory->factory(
            $staffRepository,
            $bankAccountRepository
        );
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Staff $staff, Option $invitationOption): void
    {
        $storedStaff = $this->transaction->run(function () use ($context, $staff, $invitationOption) {
            $bankAccount = $this->createBankAccount();
            $storedStaff = $this->storeStaff($context, $staff, $bankAccount);
            foreach ($invitationOption as $invitation) {
                $this->updateInvitation($context, $invitation, $storedStaff);
            }
            return $storedStaff;
        });
        $this->logger()->info(
            'スタッフが登録されました',
            ['id' => $storedStaff->id] + $context->logContext()
        );
    }

    /**
     * 銀行口座エンティティを登録する.
     *
     * @return \Domain\BankAccount\BankAccount
     */
    private function createBankAccount(): BankAccount
    {
        $bankAccount = BankAccount::create([
            'bankName' => '',
            'bankCode' => '',
            'bankBranchName' => '',
            'bankBranchCode' => '',
            'bankAccountType' => BankAccountType::unknown(),
            'bankAccountNumber' => '',
            'bankAccountHolder' => '',
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
        return $this->bankAccountRepository->store($bankAccount);
    }

    /**
     * スタッフをリポジトリに格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Staff $staff
     * @param \Domain\BankAccount\BankAccount $bankAccount
     * @return \Domain\Staff\Staff
     */
    private function storeStaff(Context $context, Staff $staff, BankAccount $bankAccount): Staff
    {
        $x = $staff->copy([
            'organizationId' => $context->organization->id,
            'bankAccountId' => $bankAccount->id,
        ]);
        return $this->staffRepository->store($x);
    }

    /**
     * 招待状に登録されたスタッフの情報を記録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Staff\Invitation $invitation
     * @param \Domain\Staff\Staff $staff
     * @return void
     */
    private function updateInvitation(Context $context, Invitation $invitation, Staff $staff): void
    {
        $this->editInvitationUseCase->handle($context, $invitation->id, ['staffId' => $staff->id]);
    }
}
