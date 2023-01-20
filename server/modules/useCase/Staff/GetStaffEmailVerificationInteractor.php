<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;
use Domain\Staff\StaffEmailVerification;
use Domain\Staff\StaffEmailVerificationRepository;
use Domain\Staff\StaffRepository;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\TokenExpiredException;

/**
 * スタッフメールアドレス検証エンティティ取得実装.
 */
final class GetStaffEmailVerificationInteractor implements GetStaffEmailVerificationUseCase
{
    private StaffEmailVerificationRepository $emailVerificationRepository;
    private StaffRepository $staffRepository;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffEmailVerificationRepository $emailVerificationRepository
     * @param \Domain\Staff\StaffRepository $staffRepository
     */
    public function __construct(
        StaffEmailVerificationRepository $emailVerificationRepository,
        StaffRepository $staffRepository
    ) {
        $this->emailVerificationRepository = $emailVerificationRepository;
        $this->staffRepository = $staffRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): StaffEmailVerification
    {
        $emailVerification = $this->emailVerificationRepository
            ->lookupOptionByToken($token)
            ->filter(function (StaffEmailVerification $x) use ($context) {
                return $this->staffRepository
                    ->lookup($x->staffId)
                    ->exists(fn (Staff $staff) => $staff->organizationId === $context->organization->id);
            })
            ->getOrElse(function () use ($token): void {
                throw new NotFoundException("StaffEmailVerification[{$token}] not found");
            });
        if ($emailVerification->expiredAt->isPast()) {
            throw new TokenExpiredException("StaffEmailVerification[{$token}] is expired");
        } else {
            return $emailVerification;
        }
    }
}
