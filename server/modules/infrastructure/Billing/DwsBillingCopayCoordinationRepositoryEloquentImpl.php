<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingCopayCoordination as DomainCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationItem as DomainCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\DwsBillingCopayCoordinationRepository} Eloquent 実装.
 */
final class DwsBillingCopayCoordinationRepositoryEloquentImpl extends EloquentRepository implements DwsBillingCopayCoordinationRepository
{
    /** {@inheritdoc} */
    public function lookupByBundleId(int ...$ids): Map
    {
        $xs = DwsBillingCopayCoordination::whereIn('dws_billing_bundle_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (DwsBillingCopayCoordination $x): DomainCopayCoordination => $x->toDomain())
            ->groupBy(fn (DomainCopayCoordination $x): int => $x->dwsBillingBundleId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = DwsBillingCopayCoordination::findMany($id);
        return Seq::fromArray($xs)->map(fn (DwsBillingCopayCoordination $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainCopayCoordination
    {
        assert($entity instanceof DomainCopayCoordination);

        $copayCoordination = DwsBillingCopayCoordination::fromDomain($entity);
        $copayCoordination->save();

        $this->storeItems($copayCoordination, $entity->items);

        return $copayCoordination->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsBillingCopayCoordination::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingInvoiceItem} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingCopayCoordination $copayCoordination
     * @param array|\Domain\Billing\DwsBillingCopayCoordinationItem[] $items
     * @return void
     */
    private function storeItems(DwsBillingCopayCoordination $copayCoordination, array $items): void
    {
        $size = count($items);
        if ($copayCoordination->items()->count() > count($items)) {
            $copayCoordination->items()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($copayCoordination, $items): Generator {
            $sorted = Seq::fromArray($items)->sortBy(fn (DomainCopayCoordinationItem $x): int => $x->itemNumber);
            foreach ($sorted as $index => $item) {
                yield DwsBillingCopayCoordinationItem::fromDomain($item, $copayCoordination->id, $index);
            }
        });
        $copayCoordination->items()->saveMany($xs);
    }
}
