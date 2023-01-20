<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\LtcsBilling;
use Infrastructure\Billing\LtcsBillingFile;

/**
 * {@link \Domain\Billing\LtcsBilling} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsBillingFixture
{
    /**
     * 介護保険サービス：請求をデータベースに登録する.
     *
     * @return void
     */
    protected function createLtcsBillings(): void
    {
        foreach ($this->examples->ltcsBillings as $billing) {
            LtcsBilling::fromDomain($billing)->saveIfNotExists();
            foreach ($billing->files as $index => $file) {
                LtcsBillingFile::fromDomain($file, $billing->id, $index)->save();
            }
        }
    }
}
