<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceCalcSpecRepository;
use ScalikePHP\Seq;

/**
 * 事業所算定情報（障害・居宅介護）ユースケース実装.
 */
final class LookupHomeHelpServiceCalcSpecInteractor implements LookupHomeHelpServiceCalcSpecUseCase
{
    private HomeHelpServiceCalcSpecRepository $repository;
    private EnsureOfficeUseCase $ensureOfficeUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     * @param \Domain\Office\HomeHelpServiceCalcSpecRepository $repository
     */
    public function __construct(EnsureOfficeUseCase $ensureOfficeUseCase, HomeHelpServiceCalcSpecRepository $repository)
    {
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $permissions, int $officeId, int ...$ids): Seq
    {
        $this->ensureOfficeUseCase->handle($context, $permissions, $officeId);
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (HomeHelpServiceCalcSpec $x): bool => $x->officeId === $officeId)
            ? $xs
            : Seq::emptySeq();
    }
}
