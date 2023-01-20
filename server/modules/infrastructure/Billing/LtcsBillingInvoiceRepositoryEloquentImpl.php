<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingInvoice as DomainBillingInvoice;
use Domain\Billing\LtcsBillingInvoiceRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\LtcsBillingInvoiceRepository} Eloquent 実装.
 */
final class LtcsBillingInvoiceRepositoryEloquentImpl extends EloquentRepository implements LtcsBillingInvoiceRepository
{
    /** {@inheritdoc} */
    public function lookupByBundleId(int ...$ids): Map
    {
        $xs = LtcsBillingInvoice::whereIn('bundle_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (LtcsBillingInvoice $x): DomainBillingInvoice => $x->toDomain())
            ->groupBy('bundleId');
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsBillingInvoice::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsBillingInvoice $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainBillingInvoice
    {
        assert($entity instanceof DomainBillingInvoice);
        $invoice = LtcsBillingInvoice::fromDomain($entity);
        $invoice->save();
        return $invoice->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsBillingInvoice::destroy($ids);
    }
}
