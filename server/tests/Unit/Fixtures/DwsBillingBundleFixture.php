<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsBillingBundle;
use Infrastructure\Billing\DwsBillingServiceDetail;

/**
 * {@link \Domain\Billing\DwsBillingBundle} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsBillingBundleFixture
{
    /**
     * 障害福祉サービス：請求単位をデータベースに格納する.
     *
     * @return void
     */
    protected function createDwsBillingBundles(): void
    {
        foreach ($this->examples->dwsBillingBundles as $bundle) {
            DwsBillingBundle::fromDomain($bundle)->save();
            foreach ($bundle->details as $index => $detail) {
                DwsBillingServiceDetail::fromDomain($detail, $bundle->id, $index)->save();
            }
        }
    }
}
