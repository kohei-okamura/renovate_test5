<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\ProvisionReport\LtcsProvisionReport as DomainLtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Infrastructure\Project\LtcsProjectAmount;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * LtcsProvisionReportRepository eloquent implementation.
 */
final class LtcsProvisionReportRepositoryEloquentImpl extends EloquentRepository implements LtcsProvisionReportRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = LtcsProvisionReport::findMany($id);
        return Seq::fromArray($xs)->map(fn (LtcsProvisionReport $x): DomainLtcsProvisionReport => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainLtcsProvisionReport
    {
        assert($entity instanceof DomainLtcsProvisionReport);
        $ltcsProvisionReport = LtcsProvisionReport::fromDomain($entity);
        if ($ltcsProvisionReport->entries()->exists()) {
            $ltcsProvisionReport->entries()->delete();
        }
        $ltcsProvisionReport->save();
        foreach ($entity->entries as $entryIndex => $domainEntry) {
            $entry = LtcsProvisionReportEntry::fromDomain(
                $domainEntry,
                [
                    'ltcs_provision_report_id' => $ltcsProvisionReport->id,
                    'sort_order' => $entryIndex,
                ]
            );
            $ltcsProvisionReport->entries()->save($entry);
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
        return $ltcsProvisionReport->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsProvisionReport::destroy($ids);
    }
}
