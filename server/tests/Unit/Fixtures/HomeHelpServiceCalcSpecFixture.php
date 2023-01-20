<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Office\HomeHelpServiceCalcSpec;
use Infrastructure\Office\HomeHelpServiceCalcSpecAttr;

/**
 * HomeHelpServiceCalcSpec fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait HomeHelpServiceCalcSpecFixture
{
    /**
     * 障害福祉サービス：居宅介護：算定情報 登録.
     */
    protected function createHomeHelpServiceCalcSpecs(): void
    {
        foreach ($this->examples->homeHelpServiceCalcSpecs as $domain) {
            $homeHelpServiceCalcSpec = HomeHelpServiceCalcSpec::fromDomain($domain)->saveIfNotExists();
            $homeHelpServiceCalcSpec->attr()->save(HomeHelpServiceCalcSpecAttr::fromDomain($domain));
        }
    }
}
