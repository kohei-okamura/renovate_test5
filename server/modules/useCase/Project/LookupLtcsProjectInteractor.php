<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectRepository;
use ScalikePHP\Seq;
use UseCase\User\EnsureUserUseCase;

/**
 * 介護保険サービス計画取得実装.
 */
final class LookupLtcsProjectInteractor implements LookupLtcsProjectUseCase
{
    private EnsureUserUseCase $ensureUserUseCase;
    private LtcsProjectRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\Project\LtcsProjectRepository $repository
     */
    public function __construct(EnsureUserUseCase $ensureUserUseCase, LtcsProjectRepository $repository)
    {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (LtcsProject $x): bool => $x->userId === $userId)
            ? $xs
            : Seq::emptySeq();
    }
}
