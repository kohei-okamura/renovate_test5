<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBilling as DomainDwsBilling;
use Domain\Billing\DwsBillingRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Seq;

/**
 * DwsBillingRepository eloquent implementation.
 */
final class DwsBillingRepositoryEloquentImpl extends EloquentRepository implements DwsBillingRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsBilling::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsBilling $x): DomainDwsBilling => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsBilling
    {
        assert($entity instanceof DomainDwsBilling);

        $billing = DwsBilling::fromDomain($entity);
        $billing->save();

        $this->storeFiles($billing, $entity->files);

        return $billing->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsBilling::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\DwsBillingFile} を保管する.
     *
     * @param \Infrastructure\Billing\DwsBilling $billing
     * @param array|\Domain\Billing\DwsBillingFile[] $files
     * @return void
     */
    private function storeFiles(DwsBilling $billing, array $files): void
    {
        $size = count($files);
        if ($billing->files()->count() > count($files)) {
            $billing->files()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($billing, $files): Generator {
            foreach ($files as $index => $file) {
                yield DwsBillingFile::fromDomain($file, $billing->id, $index);
            }
        });
        $billing->files()->saveMany($xs);
    }
}
