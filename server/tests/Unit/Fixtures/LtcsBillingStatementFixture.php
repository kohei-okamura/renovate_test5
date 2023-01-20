<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\LtcsBillingStatement;
use Infrastructure\Billing\LtcsBillingStatementAggregate;
use Infrastructure\Billing\LtcsBillingStatementAggregateSubsidy;
use Infrastructure\Billing\LtcsBillingStatementAppendix;
use Infrastructure\Billing\LtcsBillingStatementAppendixEntry;
use Infrastructure\Billing\LtcsBillingStatementItem;
use Infrastructure\Billing\LtcsBillingStatementItemSubsidy;
use Infrastructure\Billing\LtcsBillingStatementSubsidy;

/**
 * {@link \Domain\Billing\LtcsBillingStatement} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsBillingStatementFixture
{
    /**
     * 介護保険サービス：明細書をデータベースに登録する.
     *
     * @return void
     */
    protected function createLtcsBillingStatements(): void
    {
        foreach ($this->examples->ltcsBillingStatements as $statement) {
            LtcsBillingStatement::fromDomain($statement)->saveIfNotExists();
            foreach ($statement->subsidies as $i => $subsidy) {
                LtcsBillingStatementSubsidy::fromDomain($subsidy, $statement->id, $i)->save();
            }
            foreach ($statement->items as $i => $item) {
                $x = LtcsBillingStatementItem::fromDomain($item, $statement->id, $i);
                $x->save();
                foreach ($item->subsidies as $j => $subsidy) {
                    LtcsBillingStatementItemSubsidy::fromDomain($subsidy, $x->id, $j)->save();
                }
            }
            foreach ($statement->aggregates as $i => $aggregate) {
                $x = LtcsBillingStatementAggregate::fromDomain($aggregate, $statement->id, $i);
                $x->save();
                foreach ($aggregate->subsidies as $j => $subsidy) {
                    LtcsBillingStatementAggregateSubsidy::fromDomain($subsidy, $x->id, $j)->save();
                }
            }
            if ($statement->appendix !== null) {
                $appendix = LtcsBillingStatementAppendix::fromDomain($statement->appendix, $statement->id);
                $appendix->save();
                foreach ($statement->appendix->unmanagedEntries as $i => $entry) {
                    $x = LtcsBillingStatementAppendixEntry::fromDomain(
                        $entry,
                        $appendix->id,
                        LtcsBillingStatementAppendixEntry::ENTRY_TYPE_UNMANAGED,
                        $i
                    );
                    $x->save();
                }
                foreach ($statement->appendix->managedEntries as $i => $entry) {
                    $x = LtcsBillingStatementAppendixEntry::fromDomain(
                        $entry,
                        $appendix->id,
                        LtcsBillingStatementAppendixEntry::ENTRY_TYPE_MANAGED,
                        $i
                    );
                    $x->save();
                }
            }
        }
    }
}
