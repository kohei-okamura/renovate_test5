<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingStatement as DomainDwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * DwsBillingStatementRepository eloquent implementation.
 */
final class DwsBillingStatementRepositoryEloquentImpl extends EloquentRepository implements DwsBillingStatementRepository
{
    /** {@inheritdoc} */
    public function lookupByBundleId(int ...$ids): Map
    {
        $xs = DwsBillingStatement::whereIn('dws_billing_bundle_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (DwsBillingStatement $x): DomainDwsBillingStatement => $x->toDomain())
            ->groupBy(fn (DomainDwsBillingStatement $x): int => $x->dwsBillingBundleId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = DwsBillingStatement::findMany($id);
        return Seq::fromArray($xs)->map(fn (DwsBillingStatement $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsBillingStatement
    {
        assert($entity instanceof DomainDwsBillingStatement);

        $statement = DwsBillingStatement::fromDomain($entity);
        $statement->save();

        $this->storeAggregates($statement, $entity->aggregates);
        $this->storeContracts($statement, $entity->contracts);
        $this->storeItems($statement, $entity->items);

        return $statement->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsBillingStatement::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingStatementAggregate} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingStatement $statement
     * @param array|\Domain\Billing\DwsBillingStatementAggregate[] $aggregates
     * @return void
     */
    private function storeAggregates(DwsBillingStatement $statement, array $aggregates): void
    {
        $size = count($aggregates);
        if ($statement->aggregates()->count() > count($aggregates)) {
            $statement->aggregates()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($statement, $aggregates): Generator {
            foreach ($aggregates as $index => $item) {
                yield DwsBillingStatementAggregate::fromDomain($item, $statement->id, $index);
            }
        });
        $statement->aggregates()->saveMany($xs);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingStatementContract} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingStatement $statement
     * @param array|\Domain\Billing\DwsBillingStatementContract[] $contracts
     * @return void
     */
    private function storeContracts(DwsBillingStatement $statement, array $contracts): void
    {
        $size = count($contracts);
        if ($statement->contracts()->count() > count($contracts)) {
            $statement->contracts()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($statement, $contracts): Generator {
            foreach ($contracts as $index => $item) {
                yield DwsBillingStatementContract::fromDomain($item, $statement->id, $index);
            }
        });
        $statement->contracts()->saveMany($xs);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingStatementItem} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingStatement $statement
     * @param array|\Domain\Billing\DwsBillingStatementItem[] $items
     * @return void
     */
    private function storeItems(DwsBillingStatement $statement, array $items): void
    {
        $size = count($items);
        if ($statement->items()->count() > count($items)) {
            $statement->items()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($statement, $items): Generator {
            foreach ($items as $index => $item) {
                yield DwsBillingStatementItem::fromDomain($item, $statement->id, $index);
            }
        });
        $statement->items()->saveMany($xs);
    }
}
