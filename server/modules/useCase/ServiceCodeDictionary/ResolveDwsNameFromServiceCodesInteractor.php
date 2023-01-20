<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Entity;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use ScalikePHP\Map;
use ScalikePHP\Seq;

class ResolveDwsNameFromServiceCodesInteractor implements ResolveDwsNameFromServiceCodesUseCase
{
    private DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder;
    private DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder;

    /**
     * Constructor.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder
     */
    public function __construct(
        DwsHomeHelpServiceDictionaryEntryFinder $homeHelpServiceDictionaryEntryFinder,
        DwsVisitingCareForPwsdDictionaryEntryFinder $visitingCareForPwsdDictionaryEntryFinder
    ) {
        $this->homeHelpServiceDictionaryEntryFinder = $homeHelpServiceDictionaryEntryFinder;
        $this->visitingCareForPwsdDictionaryEntryFinder = $visitingCareForPwsdDictionaryEntryFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $serviceCodes): Map
    {
        $paginationParams = ['all' => true, 'sortBy' => 'id', 'desc' => true];

        // 辞書が更新されても同一のサービスコードならばサービス名称は変わらないという前提で
        // Dictionary で辞書は特定せずに、Entryだけをfindし、サービスコードが存在している最新のサービス辞書のデータを
        // 取得する という思想で実装している
        $homeHelpServiceDictionaryEntries = $this->homeHelpServiceDictionaryEntryFinder
            ->find(
                [
                    'providedIn' => Carbon::now(),
                    'serviceCodes' => Seq::from(...$serviceCodes)->map(fn (ServiceCode $x) => $x->toString())->toArray(),
                ],
                $paginationParams
            )
            ->list;

        $visitingCareForPwsdDictionaryEntries = $this->visitingCareForPwsdDictionaryEntryFinder
            ->find(
                [
                    'providedIn' => Carbon::now(),
                    'serviceCodes' => Seq::from(...$serviceCodes)->map(fn (ServiceCode $x) => $x->toString())->toArray(),
                ],
                $paginationParams
            )
            ->list;

        return Seq::from(
            ...$homeHelpServiceDictionaryEntries,
            ...$visitingCareForPwsdDictionaryEntries
        )
            ->groupBy(fn (Entity $x): string => $x->serviceCode->toString())
            ->mapValues(fn (Seq $x): string => $x->head()->name);
    }
}
