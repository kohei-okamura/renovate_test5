<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use UseCase\User\EnsureUserUseCase;

/**
 * 障害福祉サービス受給者証取得ユースケース実装.
 */
final class LookupDwsCertificationInteractor implements LookupDwsCertificationUseCase
{
    private EnsureUserUseCase $ensureUserUseCase;
    private DwscertificationRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\DwsCertification\DwscertificationRepository $repository
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        DwsCertificationRepository $repository
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $p = fn (DwsCertification $x): bool => $x->userId === $userId;
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll($p) ? $xs : Seq::emptySeq();
    }
}
