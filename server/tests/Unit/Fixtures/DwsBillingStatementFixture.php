<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsBillingStatement;
use Infrastructure\Billing\DwsBillingStatementAggregate;
use Infrastructure\Billing\DwsBillingStatementContract;
use Infrastructure\Billing\DwsBillingStatementItem;

/**
 * DwsBillingStatement fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsBillingStatementFixture
{
    /**
     * 障害福祉サービス:明細書 登録.
     *
     * @return void
     */
    protected function createDwsBillingStatements(): void
    {
        foreach ($this->examples->dwsBillingStatements as $statement) {
            DwsBillingStatement::fromDomain($statement)->save();
            foreach ($statement->aggregates as $index => $aggregate) {
                DwsBillingStatementAggregate::fromDomain($aggregate, $statement->id, $index)->save();
            }
            foreach ($statement->contracts as $index => $contract) {
                DwsBillingStatementContract::fromDomain($contract, $statement->id, $index)->save();
            }
            foreach ($statement->items as $index => $item) {
                DwsBillingStatementItem::fromDomain($item, $statement->id, $index)->save();
            }
        }
    }
}
