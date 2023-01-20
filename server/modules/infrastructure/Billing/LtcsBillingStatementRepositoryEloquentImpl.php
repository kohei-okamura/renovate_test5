<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatement as DomainBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\LtcsBillingStatementRepository} Eloquent 実装.
 */
final class LtcsBillingStatementRepositoryEloquentImpl extends EloquentRepository implements LtcsBillingStatementRepository
{
    private const FIXED_SUBSIDIES_COUNT = 3;

    /**
     * {@inheritdoc}
     */
    public function lookupByBundleId(int ...$ids): Map
    {
        $xs = LtcsBillingStatement::whereIn('bundle_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (LtcsBillingStatement $x): DomainBillingStatement => $x->toDomain())
            ->groupBy('bundleId');
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsBillingStatement::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsBillingStatement $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainBillingStatement
    {
        assert($entity instanceof DomainBillingStatement);

        $statement = LtcsBillingStatement::fromDomain($entity);
        $statement->save();

        $this->storeSubsidies($statement, $entity->subsidies);
        $this->storeItems($statement, $entity->items);
        $this->storeAggregates($statement, $entity->aggregates);
        $this->storeAppendix($statement, $entity->appendix);

        return $statement->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsBillingStatement::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\LtcsBillingStatementSubsidy} を保管する.
     *
     * @param \Infrastructure\Billing\LtcsBillingStatement $statement
     * @param array|\Domain\Billing\LtcsBillingStatementSubsidy[] $subsidies
     * @return void
     */
    private function storeSubsidies(LtcsBillingStatement $statement, array $subsidies): void
    {
        assert(count($subsidies) === self::FIXED_SUBSIDIES_COUNT);
        $xs = Arrays::generate(function () use ($statement, $subsidies): Generator {
            foreach ($subsidies as $index => $subsidy) {
                yield LtcsBillingStatementSubsidy::fromDomain($subsidy, $statement->id, $index);
            }
        });
        $statement->subsidies()->saveMany($xs);
    }

    /**
     * {@link \Infrastructure\Billing\LtcsBillingStatementItem} を保管する.
     *
     * @param \Infrastructure\Billing\LtcsBillingStatement $statement
     * @param array|\Domain\Billing\LtcsBillingStatementItem[] $items
     * @return void
     */
    private function storeItems(LtcsBillingStatement $statement, array $items): void
    {
        $size = count($items);
        if ($statement->items()->count() > count($items)) {
            $statement->items()->where('sort_order', '>', $size - 1)->delete();
        }
        foreach ($items as $index => $item) {
            $x = LtcsBillingStatementItem::fromDomain($item, $statement->id, $index);
            $x->save();
            $subsidies = Arrays::generate(function () use ($item, $x): Generator {
                foreach ($item->subsidies as $subsidyIndex => $subsidy) {
                    yield LtcsBillingStatementItemSubsidy::fromDomain($subsidy, $x->id, $subsidyIndex);
                }
            });
            $x->subsidies()->saveMany($subsidies);
        }
    }

    /**
     * {@link \Infrastructure\Billing\LtcsBillingStatementAggregate} を保管する.
     *
     * @param \Infrastructure\Billing\LtcsBillingStatement $statement
     * @param array|\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates
     * @return void
     */
    private function storeAggregates(LtcsBillingStatement $statement, array $aggregates): void
    {
        $size = count($aggregates);
        if ($statement->aggregates()->count() > count($aggregates)) {
            $statement->aggregates()->where('sort_order', '>', $size - 1)->delete();
        }
        foreach ($aggregates as $index => $aggregate) {
            $x = LtcsBillingStatementAggregate::fromDomain($aggregate, $statement->id, $index);
            $x->save();
            $subsidies = Arrays::generate(function () use ($aggregate, $x): Generator {
                foreach ($aggregate->subsidies as $subsidyIndex => $subsidy) {
                    yield LtcsBillingStatementAggregateSubsidy::fromDomain($subsidy, $x->id, $subsidyIndex);
                }
            });
            $x->subsidies()->saveMany($subsidies);
        }
    }

    /**
     * {@link \Infrastructure\Billing\LtcsBillingStatementAppendix} を保管する.
     *
     * @param \Infrastructure\Billing\LtcsBillingStatement $statement
     * @param null|\Domain\ProvisionReport\LtcsProvisionReportSheetAppendix $appendix
     * @return void
     */
    private function storeAppendix(LtcsBillingStatement $statement, ?LtcsProvisionReportSheetAppendix $appendix): void
    {
        if ($appendix !== null) {
            $x = LtcsBillingStatementAppendix::fromDomain($appendix, $statement->id);
            $x->save();
            $entries = Arrays::generate(function () use ($appendix, $x): Generator {
                foreach ($appendix->unmanagedEntries as $entryIndex => $entry) {
                    yield LtcsBillingStatementAppendixEntry::fromDomain(
                        $entry,
                        $x->id,
                        LtcsBillingStatementAppendixEntry::ENTRY_TYPE_UNMANAGED,
                        $entryIndex
                    );
                }
                foreach ($appendix->managedEntries as $entryIndex => $entry) {
                    yield LtcsBillingStatementAppendixEntry::fromDomain(
                        $entry,
                        $x->id,
                        LtcsBillingStatementAppendixEntry::ENTRY_TYPE_MANAGED,
                        $entryIndex
                    );
                }
            });
            $x->entries()->saveMany($entries);
        } elseif ($statement->appendix()->count() !== 0) {
            $statement->appendix()->delete();
        }
    }
}
