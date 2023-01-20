<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsBilling;
use Infrastructure\Billing\DwsBillingFile;

/**
 * {@link \Domain\Billing\DwsBilling} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsBillingFixture
{
    /**
     * 障害福祉サービス：請求をデータベースに格納する.
     *
     * @return void
     */
    protected function createDwsBillings(): void
    {
        foreach ($this->examples->dwsBillings as $billing) {
            DwsBilling::fromDomain($billing)->save();
            foreach ($billing->files as $index => $file) {
                DwsBillingFile::fromDomain($file, $billing->id, $index)->save();
            }
        }
    }
}
