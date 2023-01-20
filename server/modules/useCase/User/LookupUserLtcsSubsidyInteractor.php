<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\UserLtcsSubsidy;
use Domain\User\UserLtcsSubsidyRepository;
use ScalikePHP\Seq;

/**
 * 公費情報取得ユースケースの実装.
 */
final class LookupUserLtcsSubsidyInteractor implements LookupUserLtcsSubsidyUseCase
{
    private EnsureUserUseCase $ensureUserUseCase;
    private UserLtcsSubsidyRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\User\UserLtcsSubsidyRepository $repository
     */
    public function __construct(EnsureUserUseCase $ensureUserUseCase, UserLtcsSubsidyRepository $repository)
    {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $p = fn (UserLtcsSubsidy $x): bool => $x->userId === $userId;
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll($p) ? $xs : Seq::emptySeq();
    }
}
