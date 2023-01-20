<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingBundle as DomainBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\DwsBillingBundleRepository} Eloquent 実装.
 */
final class DwsBillingBundleRepositoryEloquentImpl extends EloquentRepository implements DwsBillingBundleRepository
{
    /** {@inheritdoc} */
    public function lookupByBillingId(int ...$ids): Map
    {
        $xs = DwsBillingBundle::whereIn('dws_billing_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (DwsBillingBundle $x): DomainBillingBundle => $x->toDomain())
            ->groupBy(fn (DomainBillingBundle $x): int => $x->dwsBillingId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsBillingBundle::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsBillingBundle $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainBillingBundle
    {
        assert($entity instanceof DomainBillingBundle);

        $bundle = DwsBillingBundle::fromDomain($entity);
        $bundle->save();

        $this->storeDetails($bundle, $entity->details);

        return $bundle->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsBillingBundle::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingServiceDetail} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingBundle $bundle
     * @param array|\Domain\Billing\DwsBillingServiceDetail[] $details
     * @return void
     */
    private function storeDetails(DwsBillingBundle $bundle, array $details): void
    {
        $size = count($details);
        if ($bundle->details()->count() > count($details)) {
            $bundle->details()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($bundle, $details): Generator {
            foreach ($details as $index => $detail) {
                yield DwsBillingServiceDetail::fromDomain($detail, $bundle->id, $index);
            }
        });
        $bundle->details()->saveMany($xs);
    }
}
