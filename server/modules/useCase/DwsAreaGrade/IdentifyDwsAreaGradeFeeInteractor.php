<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsAreaGrade;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsAreaGrade\DwsAreaGradeFeeFinder;
use ScalikePHP\Option;

/**
 * 障害福祉サービス：地域区分単価特定ユースケース実装.
 */
final class IdentifyDwsAreaGradeFeeInteractor implements IdentifyDwsAreaGradeFeeUseCase
{
    private DwsAreaGradeFeeFinder $finder;

    /**
     * {@link \UseCase\DwsAreaGrade\IdentifyDwsAreaGradeFeeInteractor} constructor.
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeFeeFinder $finder
     */
    public function __construct(DwsAreaGradeFeeFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $dwsAreaGradeId, Carbon $targetDate): Option
    {
        $filterParams = [
            'dwsAreaGradeId' => $dwsAreaGradeId,
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
