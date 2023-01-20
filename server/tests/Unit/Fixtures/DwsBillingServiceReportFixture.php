<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Billing\DwsBillingServiceReport;
use Infrastructure\Billing\DwsBillingServiceReportItem;
use Infrastructure\Billing\DwsBillingServiceReportPlan;
use Infrastructure\Billing\DwsBillingServiceReportResult;

/**
 * {@link \Domain\Billing\DwsBillingServiceReport} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsBillingServiceReportFixture
{
    /**
     * サービス提供実績記録票をデータベースに格納する.
     *
     * @return void
     */
    protected function createDwsBillingServiceReports(): void
    {
        foreach ($this->examples->dwsBillingServiceReports as $report) {
            $x = DwsBillingServiceReport::fromDomain($report);
            $x->save();
            $id = $x->id;

            foreach ($report->items as $index => $item) {
                DwsBillingServiceReportItem::fromDomain($item, $id, $index)->save();
            }

            $plans = DwsBillingServiceReportPlan::fromDomain($report->plan, $id);
            $x->plans()->saveMany($plans);

            $results = DwsBillingServiceReportResult::fromDomain($report->result, $id);
            $x->results()->saveMany($results);
        }
    }
}
