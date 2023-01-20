<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Invitation;
use Domain\Staff\Staff;
use Lib\Exceptions\NotFoundException;

/**
 * 招待を用いるスタッフ登録ユースケース実装.
 */
final class CreateStaffWithInvitationInteractor implements CreateStaffWithInvitationUseCase
{
    private CreateStaffUseCase $createStaffUseCase;
    private LookupInvitationUseCase $lookupInvitationUseCase;
    private EditInvitationUseCase $editInvitationUseCase;

    /**
     * {@link \UseCase\Staff\CreateStaffWithInvitationInteractor} Constructor.
     *
     * @param \UseCase\Staff\CreateStaffUseCase $createStaffUseCase
     * @param \UseCase\Staff\LookupInvitationUseCase $lookupInvitationUseCase
     * @param \UseCase\Staff\EditInvitationUseCase $editInvitationUseCase
     */
    public function __construct(
        CreateStaffUseCase $createStaffUseCase,
        LookupInvitationUseCase $lookupInvitationUseCase,
        EditInvitationUseCase $editInvitationUseCase
    ) {
        $this->createStaffUseCase = $createStaffUseCase;
        $this->lookupInvitationUseCase = $lookupInvitationUseCase;
        $this->editInvitationUseCase = $editInvitationUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $invitationId, Staff $staff): void
    {
        $invitationOption = $this->lookupInvitationUseCase
            ->handle($context, $invitationId)
            ->headOption()
            ->filter(fn (Invitation $x): bool => !$x->expiredAt->isPast());

        /** @var \Domain\Staff\Invitation $invitation */
        $invitation = $invitationOption->getOrElse(function () use ($invitationId): void {
            throw new NotFoundException("Invitation({$invitationId}) not found");
        });

        $this->createStaffUseCase->handle(
            $context,
            $staff->copy([
                'roleIds' => $invitation->roleIds,
                'email' => $invitation->email,
                'officeIds' => $invitation->officeIds,
            ]),
            $invitationOption
        );
    }
}
