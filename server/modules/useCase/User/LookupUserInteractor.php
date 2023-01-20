<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Domain\User\User;
use Domain\User\UserRepository;
use ScalikePHP\Seq;
use UseCase\Contract\FindContractUseCase;

/**
 * 利用者情報取得ユースケース実装.
 */
final class LookupUserInteractor implements LookupUserUseCase
{
    private UserRepository $repository;
    private FindContractUseCase $findUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\User\UserRepository $repository
     * @param \UseCase\Contract\FindContractUseCase $findUseCase
     */
    public function __construct(UserRepository $repository, FindContractUseCase $findUseCase)
    {
        $this->repository = $repository;
        $this->findUseCase = $findUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$id): Seq
    {
        $officeIdMap = $this->findUseCase
            ->handle($context, $permission, ['userIds' => [...$id]], ['all' => true])
            ->list
            ->map(fn (Contract $x): array => ['userId' => $x->userId, 'officeId' => $x->officeId])
            ->groupBy('userId');

        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(
            fn (User $x): bool => $context->isAccessibleTo(
                $permission,
                $x->organizationId,
                $officeIdMap->get($x->id)
                    ->getOrElseValue(Seq::emptySeq())
                    ->map(fn (array $xx): int => $xx['officeId'])
                    ->toArray()
            )
        )
            ? $xs
            : Seq::emptySeq();
    }
}
