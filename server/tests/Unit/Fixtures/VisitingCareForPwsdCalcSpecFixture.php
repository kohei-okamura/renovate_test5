<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Office\VisitingCareForPwsdCalcSpec;
use Infrastructure\Office\VisitingCareForPwsdCalcSpecAttr;

/**
 * VisitingCareForPwsdCalcSpec fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait VisitingCareForPwsdCalcSpecFixture
{
    /**
     * 障害福祉サービス：重度訪問介護：算定情報 登録.
     */
    protected function createVisitingCareForPwsdCalcSpecs(): void
    {
        foreach ($this->examples->visitingCareForPwsdCalcSpecs as $domain) {
            $visitingCareForPwsdCalcSpec = VisitingCareForPwsdCalcSpec::fromDomain($domain)->saveIfNotExists();
            $visitingCareForPwsdCalcSpec->attr()->save(VisitingCareForPwsdCalcSpecAttr::fromDomain($domain));
        }
    }
}
