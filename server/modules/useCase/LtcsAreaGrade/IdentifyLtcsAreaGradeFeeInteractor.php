<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsAreaGrade;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\LtcsAreaGrade\LtcsAreaGradeFeeFinder;
use ScalikePHP\Option;

/**
 * 介護保険サービス：地域区分単価特定ユースケース実装.
 */
final class IdentifyLtcsAreaGradeFeeInteractor implements IdentifyLtcsAreaGradeFeeUseCase
{
    private LtcsAreaGradeFeeFinder $finder;

    /**
     * {@link \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeInteractor} constructor.
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeFeeFinder $finder
     */
    public function __construct(LtcsAreaGradeFeeFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $ltcsAreaGradeId, Carbon $targetDate): Option
    {
        $filterParams = [
            'ltcsAreaGradeId' => $ltcsAreaGradeId,
            'effectivatedBefore' => $targetDate,
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'effectivatedOn',
            'desc' => true,
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}
