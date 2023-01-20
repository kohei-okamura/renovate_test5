<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Common\Password;
use Domain\Context\Context;
use Domain\Staff\Staff;
use Domain\Staff\StaffPasswordResetRepository;
use Domain\Staff\StaffRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * スタッフパスワード再設定実装.
 */
final class ResetStaffPasswordInteractor implements ResetStaffPasswordUseCase
{
    use Logging;

    private GetStaffPasswordResetUseCase $getStaffPasswordResetUseCase;
    private StaffRepository $staffRepository;
    private StaffPasswordResetRepository $passwordResetRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \Domain\Staff\StaffPasswordResetRepository $passwordResetRepository
     * @param \UseCase\Staff\GetStaffPasswordResetUseCase $getStaffPasswordResetUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        StaffRepository $staffRepository,
        StaffPasswordResetRepository $passwordResetRepository,
        GetStaffPasswordResetUseCase $getStaffPasswordResetUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->staffRepository = $staffRepository;
        $this->passwordResetRepository = $passwordResetRepository;
        $this->getStaffPasswordResetUseCase = $getStaffPasswordResetUseCase;
        $this->transaction = $transactionManagerFactory->factory($passwordResetRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token, string $password): void
    {
        $x = $this->transaction->run(function () use ($context, $token, $password): Staff {
            $passwordReset = $this->getStaffPasswordResetUseCase->handle($context, $token);
            $staff = $this->staffRepository->lookup($passwordReset->staffId)->headOption()->getOrElse(
                function () use ($passwordReset) {
                    throw new NotFoundException("Staff[{$passwordReset->staffId}] not found");
                }
            );
            $values = [
                'password' => Password::fromString($password),
                'version' => $staff->version + 1,
                'updatedAt' => Carbon::now(),
            ];
            return $this->staffRepository->store($staff->copy($values));
        });
        $this->logger()->info(
            'スタッフパスワードが更新されました',
            ['id' => $x->id] + $context->logContext()
        );
    }
}
