<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス名称導出ユースケース実装.
 */
class ResolveLtcsNameFromServiceCodesInteractor implements ResolveLtcsNameFromServiceCodesUseCase
{
    private LtcsHomeVisitLongTermCareDictionaryEntryFinder $ltcsDictionaryEntryFinder;

    /**
     * Constructor.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder $ltcsDictionaryEntryFinder
     */
    public function __construct(
        LtcsHomeVisitLongTermCareDictionaryEntryFinder $ltcsDictionaryEntryFinder
    ) {
        $this->ltcsDictionaryEntryFinder = $ltcsDictionaryEntryFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $serviceCodes, Carbon $providedIn): Map
    {
        $entries = $this->ltcsDictionaryEntryFinder
            ->find(
                [
                    'providedIn' => $providedIn,
                    'serviceCodes' => $serviceCodes->map(fn (ServiceCode $x) => $x->toString())->toArray(),
                ],
                ['all' => true, 'sortBy' => 'id', 'desc' => true]
            )
            ->list;

        return $entries
            ->groupBy(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
            ->mapValues(fn (Seq $x): string => $x->head()->name);
    }
}
