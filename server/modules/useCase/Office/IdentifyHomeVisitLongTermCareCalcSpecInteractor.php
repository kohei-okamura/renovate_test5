<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareCalcSpecFinder;
use Domain\Office\Office;
use ScalikePHP\Option;

/**
 * 介護保険サービス：訪問介護：算定情報特定ユースケース実装.
 */
final class IdentifyHomeVisitLongTermCareCalcSpecInteractor implements IdentifyHomeVisitLongTermCareCalcSpecUseCase
{
    private HomeVisitLongTermCareCalcSpecFinder $finder;

    /**
     * {@link \UseCase\Office\IdentifyHomeVisitLongTermCareCalcSpecInteractor} constructor.
     *
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpecFinder $finder
     */
    public function __construct(HomeVisitLongTermCareCalcSpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Office $office, Carbon $targetDate): Option
    {
        $filterParams = [
            'officeId' => $office->id,
            'period' => $targetDate,
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
            'desc' => true,
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}
