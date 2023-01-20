<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use ScalikePHP\Seq;

/**
 * 事業所算定情報（障害・重度訪問介護）ユースケース実装.
 */
final class LookupVisitingCareForPwsdCalcSpecInteractor implements LookupVisitingCareForPwsdCalcSpecUseCase
{
    private EnsureOfficeUseCase $ensureOfficeUseCase;
    private VisitingCareForPwsdCalcSpecRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     * @param \Domain\Office\VisitingCareForPwsdCalcSpecRepository $repository
     */
    public function __construct(EnsureOfficeUseCase $ensureOfficeUseCase, VisitingCareForPwsdCalcSpecRepository $repository)
    {
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $permissions, int $officeId, int ...$ids): Seq
    {
        $this->ensureOfficeUseCase->handle($context, $permissions, $officeId);
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (VisitingCareForPwsdCalcSpec $x): bool => $x->officeId === $officeId)
            ? $xs
            : Seq::empty();
    }
}
