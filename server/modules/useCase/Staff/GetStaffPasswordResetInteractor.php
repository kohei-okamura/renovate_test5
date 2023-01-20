<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;
use Domain\Staff\StaffPasswordReset;
use Domain\Staff\StaffPasswordResetRepository;
use Domain\Staff\StaffRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\TokenExpiredException;

/**
 * スタッフパスワード再設定取得実装.
 */
final class GetStaffPasswordResetInteractor implements GetStaffPasswordResetUseCase
{
    private StaffPasswordResetRepository $passwordResetRepository;
    private StaffRepository $staffRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffPasswordResetRepository $passwordResetRepository
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        StaffPasswordResetRepository $passwordResetRepository,
        StaffRepository $staffRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->passwordResetRepository = $passwordResetRepository;
        $this->staffRepository = $staffRepository;
        $this->transaction = $transactionManagerFactory->factory($passwordResetRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): StaffPasswordReset
    {
        return $this->transaction->run(function () use ($context, $token) {
            $passwordReset = $this->passwordResetRepository
                ->lookupOptionByToken($token)
                ->filter(function (StaffPasswordReset $x) use ($context) {
                    return $this->staffRepository
                        ->lookup($x->staffId)
                        ->exists(fn (Staff $staff) => $staff->organizationId === $context->organization->id);
                })
                ->getOrElse(function () use ($token): void {
                    throw new NotFoundException("StaffPasswordReset[{$token}] not found");
                });
            if ($passwordReset->expiredAt->isPast()) {
                throw new TokenExpiredException("StaffPasswordReset[{$token}] is expired");
            } else {
                return $passwordReset;
            }
        });
    }
}
