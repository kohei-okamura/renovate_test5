<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\Office\VisitingCareForPwsdCalcSpecFinder;
use ScalikePHP\Option;

/**
 * 障害福祉サービス：重度訪問介護：算定情報特定ユースケース実装.
 */
final class IdentifyVisitingCareForPwsdCalcSpecInteractor implements IdentifyVisitingCareForPwsdCalcSpecUseCase
{
    private VisitingCareForPwsdCalcSpecFinder $finder;

    /**
     * {@link \UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecInteractor} constructor.
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpecFinder $finder
     */
    public function __construct(VisitingCareForPwsdCalcSpecFinder $finder)
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
