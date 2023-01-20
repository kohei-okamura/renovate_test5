<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\LtcsAreaGrade\LtcsAreaGradeRepository;
use ScalikePHP\Seq;

/**
 * 介保地域区分ユースケース実装.
 */
final class LookupLtcsAreaGradeInteractor implements LookupLtcsAreaGradeUseCase
{
    private LtcsAreaGradeRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeRepository $repository
     */
    public function __construct(LtcsAreaGradeRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$ids): Seq
    {
        return $this->repository->lookup(...$ids);
    }
}
