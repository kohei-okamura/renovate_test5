<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingServiceReport as DomainDwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregate as DomainAggregate;
use Domain\Billing\DwsBillingServiceReportRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportRepository} Eloquent 実装.
 */
final class DwsBillingServiceReportRepositoryEloquentImpl extends EloquentRepository implements DwsBillingServiceReportRepository
{
    /** {@inheritdoc} */
    public function lookupByBundleId(int ...$ids): Map
    {
        $xs = DwsBillingServiceReport::whereIn('dws_billing_bundle_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (DwsBillingServiceReport $x): DomainDwsBillingServiceReport => $x->toDomain())
            ->groupBy(fn (DomainDwsBillingServiceReport $x): int => $x->dwsBillingBundleId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = DwsBillingServiceReport::findMany($id);
        return Seq::fromArray($xs)->map(fn (DwsBillingServiceReport $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsBillingServiceReport
    {
        assert($entity instanceof DomainDwsBillingServiceReport);

        $report = DwsBillingServiceReport::fromDomain($entity);
        $report->save();

        $this->storeItems($report, $entity->items);
        $this->storePlans($report, $entity->plan);
        $this->storeResults($report, $entity->result);

        return $report->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsBillingServiceReport::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingServiceReportItem} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingServiceReport $report
     * @param array|\Domain\Billing\DwsBillingServiceReportItem[] $items
     * @return void
     */
    private function storeItems(DwsBillingServiceReport $report, array $items): void
    {
        $size = count($items);
        if ($report->items()->count() > count($items)) {
            $report->items()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($report, $items): Generator {
            foreach ($items as $index => $file) {
                yield DwsBillingServiceReportItem::fromDomain($file, $report->id, $index);
            }
        });
        $report->items()->saveMany($xs);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingServiceReportPlan} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingServiceReport $report
     * @param \Domain\Billing\DwsBillingServiceReportAggregate $plan
     * @return void
     */
    private function storePlans(DwsBillingServiceReport $report, DomainAggregate $plan): void
    {
        $xs = DwsBillingServiceReportPlan::fromDomain($plan, $report->id);
        $size = count($xs);
        if ($report->plans()->count() > $size) {
            $report->plans()->where('sort_order', '>', $size - 1)->delete();
        }
        $report->plans()->saveMany($xs);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingServiceReportResult} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingServiceReport $report
     * @param \Domain\Billing\DwsBillingServiceReportAggregate $result
     * @return void
     */
    private function storeResults(DwsBillingServiceReport $report, DomainAggregate $result): void
    {
        $xs = DwsBillingServiceReportResult::fromDomain($result, $report->id);
        $size = count($xs);
        if ($report->results()->count() > $size) {
            $report->results()->where('sort_order', '>', $size - 1)->delete();
        }
        $report->results()->saveMany($xs);
    }
}
