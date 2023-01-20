<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsBillingCopayCoordination;
use Infrastructure\Billing\DwsBillingCopayCoordinationItem;

/**
 * {@link \Domain\Billing\DwsBillingCopayCoordination} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsBillingCopayCoordinationFixture
{
    /**
     * 利用者負担上限額管理結果票をデータベースに格納する.
     *
     * @return void
     */
    protected function createDwsBillingCopayCoordinations(): void
    {
        foreach ($this->examples->dwsBillingCopayCoordinations as $copayCoordination) {
            DwsBillingCopayCoordination::fromDomain($copayCoordination)->save();
            foreach ($copayCoordination->items as $index => $item) {
                DwsBillingCopayCoordinationItem::fromDomain($item, $copayCoordination->id, $index)->save();
            }
        }
    }
}
