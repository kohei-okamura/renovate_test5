<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCard as DomainLtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * LtcsInsCardRepository eloquent implementation.
 */
final class LtcsInsCardRepositoryEloquentImpl extends EloquentRepository implements LtcsInsCardRepository
{
    /**
     * {@inheritdoc}
     */
    public function lookupByUserId(int ...$ids): Map
    {
        $xs = LtcsInsCard::whereIn('user_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (LtcsInsCard $x): DomainLtcsInsCard => $x->toDomain())
            ->groupBy(fn (DomainLtcsInsCard $x): int => $x->userId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsInsCard::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsInsCard $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainLtcsInsCard
    {
        assert($entity instanceof DomainLtcsInsCard);
        $x = LtcsInsCard::fromDomain($entity)->saveIfNotExists();

        /** @var \Infrastructure\LtcsInsCard\LtcsInsCardAttr $attr */
        $attr = $x->attr()->save(LtcsInsCardAttr::fromDomain($entity));

        foreach ($entity->maxBenefitQuotas as $key => $domainMaxBenefitQuota) {
            $maxBenefitQuota = LtcsInsCardMaxBenefitQuota::fromDomain($domainMaxBenefitQuota, ['sort_order' => $key]);
            $attr->maxBenefitQuotas()->save($maxBenefitQuota);
        }
        return $x->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsInsCardAttr::whereIn('ltcs_ins_card_id', $ids)->delete();
        LtcsInsCard::destroy($ids);
    }
}
