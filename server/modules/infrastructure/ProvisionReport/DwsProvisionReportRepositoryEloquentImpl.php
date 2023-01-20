<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\ProvisionReport\DwsProvisionReport as DomainDwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsProvisionReportRepository eloquent implementation.
 */
class DwsProvisionReportRepositoryEloquentImpl extends EloquentRepository implements DwsProvisionReportRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsProvisionReport::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsProvisionReport $x): DomainDwsProvisionReport => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsProvisionReport
    {
        assert($entity instanceof DomainDwsProvisionReport);
        $dwsProvisionReport = DwsProvisionReport::fromDomain($entity);
        if ($dwsProvisionReport->plans()->exists()) {
            $dwsProvisionReport->plans()->delete();
        }
        if ($dwsProvisionReport->results()->exists()) {
            $dwsProvisionReport->results()->delete();
        }
        $dwsProvisionReport->save();
        foreach ($entity->plans as $entryIndex => $domainEntry) {
            $plan = DwsProvisionReportItemPlan::fromDomain($domainEntry, $dwsProvisionReport->id, $entryIndex);
            $dwsProvisionReport->plans()->save($plan);
            $plan->syncServiceOptions($domainEntry->options);
        }
        foreach ($entity->results as $entryIndex => $domainEntry) {
            $result = DwsProvisionReportItemResult::fromDomain($domainEntry, $dwsProvisionReport->id, $entryIndex);
            $dwsProvisionReport->results()->save($result);
            $result->syncServiceOptions($domainEntry->options);
        }
        return $dwsProvisionReport->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsProvisionReport::destroy($ids);
    }
}
