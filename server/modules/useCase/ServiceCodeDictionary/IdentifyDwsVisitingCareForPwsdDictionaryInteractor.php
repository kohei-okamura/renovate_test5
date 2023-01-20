<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder;
use ScalikePHP\Option;

/**
 * 重度訪問介護サービスコード辞書特定 ユースケース実装.
 */
class IdentifyDwsVisitingCareForPwsdDictionaryInteractor implements IdentifyDwsVisitingCareForPwsdDictionaryUseCase
{
    private DwsVisitingCareForPwsdDictionaryFinder $visitingCareForPwsdDictionaryFinder;

    /**
     * constructor.
     *
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder $visitingCareForPwsdDictionaryFinder
     */
    public function __construct(DwsVisitingCareForPwsdDictionaryFinder $visitingCareForPwsdDictionaryFinder)
    {
        $this->visitingCareForPwsdDictionaryFinder = $visitingCareForPwsdDictionaryFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Carbon $targetDate): Option
    {
        return $this->visitingCareForPwsdDictionaryFinder
            ->find(['effectivatedBefore' => $targetDate], ['itemsPerPage' => 1, 'sortBy' => 'effectivatedOn', 'desc' => true])
            ->list
            ->headOption();
    }
}
