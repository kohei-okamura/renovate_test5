<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingInvoice as DomainDwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * DwsBillingInvoiceRepository eloquent implementation.
 */
final class DwsBillingInvoiceRepositoryEloquentImpl extends EloquentRepository implements DwsBillingInvoiceRepository
{
    /** {@inheritdoc} */
    public function lookupByBundleId(int ...$ids): Map
    {
        $xs = DwsBillingInvoice::whereIn('dws_billing_bundle_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (DwsBillingInvoice $x): DomainDwsBillingInvoice => $x->toDomain())
            ->groupBy(fn (DomainDwsBillingInvoice $x): int => $x->dwsBillingBundleId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = DwsBillingInvoice::findMany($id);
        return Seq::fromArray($xs)->map(fn (DwsBillingInvoice $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsBillingInvoice
    {
        assert($entity instanceof DomainDwsBillingInvoice);

        $invoice = DwsBillingInvoice::fromDomain($entity);
        $invoice->save();

        $this->storeItems($invoice, $entity->items);

        return $invoice->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsBillingInvoice::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingInvoiceItem} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBillingInvoice $invoice
     * @param array|\Domain\Billing\DwsBillingInvoiceItem[] $items
     * @return void
     */
    private function storeItems(DwsBillingInvoice $invoice, array $items): void
    {
        $size = count($items);
        if ($invoice->items()->count() > count($items)) {
            $invoice->items()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($invoice, $items): Generator {
            foreach ($items as $index => $item) {
                yield DwsBillingInvoiceItem::fromDomain($item, $invoice->id, $index);
            }
        });
        $invoice->items()->saveMany($xs);
    }
}
