<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Office\HomeVisitLongTermCareCalcSpec;
use Infrastructure\Office\HomeVisitLongTermCareCalcSpecAttr;

/**
 * HomeVisitLongTermCareCalcSpec fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait HomeVisitLongTermCareCalcSpecFixture
{
    /**
     * 介護保険：訪問介護：算定情報 登録.
     */
    protected function createHomeVisitLongTermCareCalcSpecs(): void
    {
        foreach ($this->examples->homeVisitLongTermCareCalcSpecs as $domain) {
            $homeVisitLongTermCareCalcSpec = HomeVisitLongTermCareCalcSpec::fromDomain($domain)->saveIfNotExists();
            $homeVisitLongTermCareCalcSpec->attr()->save(HomeVisitLongTermCareCalcSpecAttr::fromDomain($domain));
        }
    }
}
