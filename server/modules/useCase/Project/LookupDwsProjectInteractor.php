<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectRepository;
use ScalikePHP\Seq;
use UseCase\User\EnsureUserUseCase;

/**
 * 障害福祉サービス：計画取得実装.
 */
final class LookupDwsProjectInteractor implements LookupDwsProjectUseCase
{
    private EnsureUserUseCase $ensureUserUseCase;
    private DwsProjectRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\Project\DwsProjectRepository $repository
     */
    public function __construct(EnsureUserUseCase $ensureUserUseCase, DwsProjectRepository $repository)
    {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (DwsProject $x): bool => $x->userId === $userId)
            ? $xs
            : Seq::emptySeq();
    }
}
