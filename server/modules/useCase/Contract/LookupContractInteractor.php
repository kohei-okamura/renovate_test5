<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractRepository;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use UseCase\User\EnsureUserUseCase;

/**
 * 契約情報取得ユースケース実装.
 */
final class LookupContractInteractor implements LookupContractUseCase
{
    private ContractRepository $repository;
    private EnsureUserUseCase $ensureUserUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\Contract\ContractRepository $repository
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     */
    public function __construct(ContractRepository $repository, EnsureUserUseCase $ensureUserUseCase)
    {
        $this->repository = $repository;
        $this->ensureUserUseCase = $ensureUserUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$id): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $p = fn (Contract $x): bool => $x->organizationId === $context->organization->id
            && $x->userId === $userId;
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll($p) ? $xs : Seq::emptySeq();
    }
}
