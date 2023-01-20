<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use ScalikePHP\Seq;

/**
 * 事業所算定情報（介保・訪問介護）取得実装.
 */
final class LookupHomeVisitLongTermCareCalcSpecInteractor implements LookupHomeVisitLongTermCareCalcSpecUseCase
{
    private EnsureOfficeUseCase $ensureOfficeUseCase;
    private HomeVisitLongTermCareCalcSpecRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpecRepository $repository
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     */
    public function __construct(
        HomeVisitLongTermCareCalcSpecRepository $repository,
        EnsureOfficeUseCase $ensureOfficeUseCase
    ) {
        $this->repository = $repository;
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $permissions, int $officeId, int ...$ids): Seq
    {
        $this->ensureOfficeUseCase->handle($context, $permissions, $officeId);
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (HomeVisitLongTermCareCalcSpec $x): bool => $x->officeId === $officeId)
            ? $xs
            : Seq::emptySeq();
    }
}
