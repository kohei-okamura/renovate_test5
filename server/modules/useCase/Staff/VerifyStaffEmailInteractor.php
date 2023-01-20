<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Staff\Staff;
use Domain\Staff\StaffRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * スタッフメールアドレス検証実装.
 */
final class VerifyStaffEmailInteractor implements VerifyStaffEmailUseCase
{
    use Logging;

    private GetStaffEmailVerificationUseCase $getStaffEmailVerificationUseCase;
    private StaffRepository $staffRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \UseCase\Staff\GetStaffEmailVerificationUseCase $getStaffEmailVerificationUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        StaffRepository $staffRepository,
        GetStaffEmailVerificationUseCase $getStaffEmailVerificationUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->staffRepository = $staffRepository;
        $this->getStaffEmailVerificationUseCase = $getStaffEmailVerificationUseCase;
        $this->transaction = $transactionManagerFactory->factory($staffRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): void
    {
        $x = $this->transaction->run(function () use ($context, $token): Staff {
            $emailVerification = $this->getStaffEmailVerificationUseCase->handle($context, $token);
            $staff = $this->staffRepository->lookup($emailVerification->staffId)->headOption()->getOrElse(
                function () use ($emailVerification) {
                    throw new NotFoundException("Staff[{$emailVerification->staffId}] not found");
                }
            );
            $values = [
                'isVerified' => true,
                'version' => $staff->version + 1,
                'updatedAt' => Carbon::now(),
            ];
            return $this->staffRepository->store($staff->copy($values));
        });
        $this->logger()->info(
            'スタッフが更新されました',
            ['id' => $x->id] + $context->logContext()
        );
    }
}
