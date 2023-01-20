<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder;
use ScalikePHP\Option;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書特定ユースケース実装.
 */
final class IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor implements IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase
{
    private LtcsHomeVisitLongTermCareDictionaryFinder $finder;

    /**
     * {@link \UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryInteractor} constructor.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder $finder
     */
    public function __construct(LtcsHomeVisitLongTermCareDictionaryFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Carbon $targetDate): Option
    {
        $filterParams = ['effectivatedBefore' => $targetDate];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
            'desc' => true,
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}
