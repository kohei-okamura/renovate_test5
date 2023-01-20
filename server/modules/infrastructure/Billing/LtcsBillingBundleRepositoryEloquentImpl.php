<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingBundle as DomainBillingBundle;
use Domain\Billing\LtcsBillingBundleRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\LtcsBillingBundleRepository} Eloquent 実装.
 */
final class LtcsBillingBundleRepositoryEloquentImpl extends EloquentRepository implements LtcsBillingBundleRepository
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function lookupByBillingId(int ...$ids): Map
    {
        $xs = LtcsBillingBundle::whereIn('billing_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (LtcsBillingBundle $x): DomainBillingBundle => $x->toDomain())
            ->groupBy(fn (DomainBillingBundle $x): int => $x->billingId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsBillingBundle::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsBillingBundle $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainBillingBundle
    {
        assert($entity instanceof DomainBillingBundle);

        $bundle = LtcsBillingBundle::fromDomain($entity);
        $bundle->save();

        $this->storeDetails($bundle, $entity->details);

        return $bundle->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsBillingBundle::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\LtcsBillingServiceDetail} を保管する.
     *
     * @param \Infrastructure\Billing\LtcsBillingBundle $bundle
     * @param array|\Domain\Billing\LtcsBillingServiceDetail[] $details
     * @return void
     */
    private function storeDetails(LtcsBillingBundle $bundle, array $details): void
    {
        $size = count($details);
        if ($bundle->details()->count() > count($details)) {
            $bundle->details()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($bundle, $details): Generator {
            foreach ($details as $index => $detail) {
                yield LtcsBillingServiceDetail::fromDomain($detail, $bundle->id, $index);
            }
        });
        $bundle->details()->saveMany($xs);
    }
}
