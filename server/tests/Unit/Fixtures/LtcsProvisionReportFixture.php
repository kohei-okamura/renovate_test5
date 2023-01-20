<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Project\LtcsProjectAmount;
use Infrastructure\ProvisionReport\LtcsProvisionReport;
use Infrastructure\ProvisionReport\LtcsProvisionReportEntry;
use Infrastructure\ProvisionReport\LtcsProvisionReportEntryPlan;
use Infrastructure\ProvisionReport\LtcsProvisionReportEntryResult;

/**
 * LtcsProvisionReport fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsProvisionReportFixture
{
    /**
     * 介護保険サービス：予実 登録.
     *
     * @return void
     */
    protected function createLtcsProvisionReports(): void
    {
        foreach ($this->examples->ltcsProvisionReports as $entity) {
            $x = LtcsProvisionReport::fromDomain($entity);
            $x->save();
            foreach ($entity->entries as $entryIndex => $domainEntry) {
                $entry = LtcsProvisionReportEntry::fromDomain(
                    $domainEntry,
                    [
                        'ltcs_provision_report_id' => $x->id,
                        'sort_order' => $entryIndex,
                    ]
                );
                $x->entries()->save($entry);
                $entry->syncServiceOptions($domainEntry->options);
                foreach ($domainEntry->amounts as $amountIndex => $domainAmount) {
                    $amount = LtcsProjectAmount::fromDomain(
                        $domainAmount,
                        [
                            'ltcs_provision_report_entry_id' => $entry->id,
                            'sort_order' => $amountIndex,
                        ]
                    );
                    $entry->amounts()->save($amount);
                }
                foreach ($domainEntry->plans as $planIndex => $carbonPlan) {
                    $plan = LtcsProvisionReportEntryPlan::fromDomain(
                        $carbonPlan,
                        [
                            'ltcs_provision_report_entry_id' => $entry->id,
                            'sort_order' => $planIndex,
                        ]
                    );
                    $entry->plans()->save($plan);
                }
                foreach ($domainEntry->results as $resultIndex => $carbonResult) {
                    $result = LtcsProvisionReportEntryResult::fromDomain(
                        $carbonResult,
                        [
                            'ltcs_provision_report_entry_id' => $entry->id,
                            'sort_order' => $resultIndex,
                        ]
                    );
                    $entry->results()->save($result);
                }
            }
        }
    }
}
