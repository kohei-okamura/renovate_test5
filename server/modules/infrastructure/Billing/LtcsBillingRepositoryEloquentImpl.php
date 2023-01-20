<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBilling as DomainBilling;
use Domain\Billing\LtcsBillingRepository;
use Generator;
use Infrastructure\Repository\EloquentRepository;
use Lib\Arrays;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\LtcsBillingRepository} Eloquent 実装.
 */
final class LtcsBillingRepositoryEloquentImpl extends EloquentRepository implements LtcsBillingRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsBilling::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsBilling $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainBilling
    {
        assert($entity instanceof DomainBilling);
        $billing = LtcsBilling::fromDomain($entity);
        $billing->save();

        $this->storeFiles($billing, $entity->files);

        return $billing->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsBilling::destroy($ids);
    }

    /**
     * {@link \Infrastructure\Billing\LtcsBillingFile} を保管する.
     *
     * @param \Infrastructure\Billing\LtcsBilling $billing
     * @param array|\Domain\Billing\LtcsBillingFile[] $files
     * @return void
     */
    private function storeFiles(LtcsBilling $billing, array $files): void
    {
        $size = count($files);
        if ($billing->files()->count() > count($files)) {
            $billing->files()->where('sort_order', '>', $size - 1)->delete();
        }
        $xs = Arrays::generate(function () use ($billing, $files): Generator {
            foreach ($files as $index => $file) {
                yield LtcsBillingFile::fromDomain($file, $billing->id, $index);
            }
        });
        $billing->files()->saveMany($xs);
    }
}
