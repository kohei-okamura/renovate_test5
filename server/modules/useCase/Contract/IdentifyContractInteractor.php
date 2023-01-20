<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractRepository;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use UseCase\Office\EnsureOfficeUseCase;
use UseCase\User\EnsureUserUseCase;

/**
 * 契約特定の実装.
 */
class IdentifyContractInteractor implements IdentifyContractUseCase
{
    private ContractRepository $contractRepository;
    private EnsureUserUseCase $ensureUserUseCase;
    private EnsureOfficeUseCase $ensureOfficeUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\Contract\ContractRepository $contractRepository
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     */
    public function __construct(
        ContractRepository $contractRepository,
        EnsureUserUseCase $ensureUserUseCase,
        EnsureOfficeUseCase $ensureOfficeUseCase
    ) {
        $this->contractRepository = $contractRepository;
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Permission $permission,
        int $officeId,
        int $userId,
        ServiceSegment $serviceSegment,
        Carbon $targetDate
    ): Option {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $this->ensureOfficeUseCase->handle($context, [$permission], $officeId);

        $lookupMap = $this->contractRepository->lookupByUserId($userId);
        if ($lookupMap->isEmpty()) {
            return Option::none();
        }
        $contracts = $lookupMap->head()[1];
        return $contracts->filter(fn (Contract $x): bool => in_array($x->status, [ContractStatus::formal(), ContractStatus::provisional(), ContractStatus::terminated()], true))
            ->filter(fn (Contract $x): bool => $x->officeId === $officeId)
            ->filter(fn (Contract $x): bool => $x->serviceSegment === $serviceSegment)
            ->filter(fn (Contract $x): bool => $x->contractedOn === null || $x->contractedOn <= $targetDate)
            ->filter(fn (Contract $x): bool => $x->terminatedOn === null || $x->terminatedOn >= $targetDate->startOfMonth())
            ->takeRight(1)
            ->headOption();
    }
}
