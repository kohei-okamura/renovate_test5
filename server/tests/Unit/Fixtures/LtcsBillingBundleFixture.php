<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\LtcsBillingBundle;
use Infrastructure\Billing\LtcsBillingServiceDetail;

/**
 * {@link \Domain\Billing\LtcsBillingBundle} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsBillingBundleFixture
{
    /**
     * 介護保険サービス：請求単位をデータベースに登録する.
     *
     * @return void
     */
    protected function createLtcsBillingBundles(): void
    {
        foreach ($this->examples->ltcsBillingBundles as $bundle) {
            LtcsBillingBundle::fromDomain($bundle)->saveIfNotExists();
            foreach ($bundle->details as $index => $detail) {
                LtcsBillingServiceDetail::fromDomain($detail, $bundle->id, $index)->save();
            }
        }
    }
}
