<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\LtcsBillingInvoice;

/**
 * {@link \Domain\Billing\LtcsBillingInvoice} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsBillingInvoiceFixture
{
    /**
     * 介護保険サービス：請求書をデータベースに登録する.
     *
     * @return void
     */
    protected function createLtcsBillingInvoices(): void
    {
        foreach ($this->examples->ltcsBillingInvoices as $invoice) {
            LtcsBillingInvoice::fromDomain($invoice)->saveIfNotExists();
        }
    }
}
