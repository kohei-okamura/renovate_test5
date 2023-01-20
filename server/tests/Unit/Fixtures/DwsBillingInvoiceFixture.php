<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsBillingInvoice;
use Infrastructure\Billing\DwsBillingInvoiceItem;

/**
 * {@link \Domain\Billing\DwsBillingInvoice} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsBillingInvoiceFixture
{
    /**
     * 障害福祉サービス：請求書をデータベースに格納する.
     *
     * @return void
     */
    protected function createDwsBillingInvoices(): void
    {
        foreach ($this->examples->dwsBillingInvoices as $invoice) {
            DwsBillingInvoice::fromDomain($invoice)->save();
            foreach ($invoice->items as $index => $item) {
                DwsBillingInvoiceItem::fromDomain($item, $invoice->id, $index)->save();
            }
        }
    }
}
