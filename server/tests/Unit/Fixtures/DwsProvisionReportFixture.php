<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\ProvisionReport\DwsProvisionReport;
use Infrastructure\ProvisionReport\DwsProvisionReportItemPlan;
use Infrastructure\ProvisionReport\DwsProvisionReportItemResult;

/**
 * DwsProvisionReport fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsProvisionReportFixture
{
    /**
     * 障害福祉サービス：予実 登録.
     *
     * @return void
     */
    protected function createDwsProvisionReports(): void
    {
        foreach ($this->examples->dwsProvisionReports as $entity) {
            $x = DwsProvisionReport::fromDomain($entity);
            $x->save();
            foreach ($entity->plans as $entryIndex => $domainEntry) {
                $plan = DwsProvisionReportItemPlan::fromDomain($domainEntry, $x->id, $entryIndex);
                $x->plans()->save($plan);
                $plan->syncServiceOptions($domainEntry->options);
            }
            foreach ($entity->results as $entryIndex => $domainEntry) {
                $result = DwsProvisionReportItemResult::fromDomain($domainEntry, $x->id, $entryIndex);
                $x->results()->save($result);
                $result->syncServiceOptions($domainEntry->options);
            }
        }
    }
}
