<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\DwsCertification\DwsCertification as DomainDwsCertification;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\DwsCertification\DwsType;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * DwsCertificationRepository eloquent implementation.
 */
final class DwsCertificationRepositoryEloquentImpl extends EloquentRepository implements DwsCertificationRepository
{
    /**
     * {@inheritdoc}
     */
    public function lookupByUserId(int ...$ids): Map
    {
        $xs = DwsCertification::whereIn('user_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (DwsCertification $x): DomainDwsCertification => $x->toDomain())
            ->groupBy(fn (DomainDwsCertification $x): int => $x->userId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $x = DwsCertification::findMany($ids);
        return Seq::fromArray($x)->map(fn (DwsCertification $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsCertification
    {
        assert($entity instanceof DomainDwsCertification);

        /** @var \Infrastructure\DwsCertification\DwsCertification $certification */
        $certification = DwsCertification::fromDomain($entity)->saveIfNotExists();

        /** @var \Infrastructure\DwsCertification\DwsCertificationAttr $attr */
        $attr = $certification->attr()->save(DwsCertificationAttr::fromDomain($entity));

        $types = Seq::fromArray($entity->dwsTypes)->map(
            fn (DwsType $x) => DwsCertificationAttrDwsType::fromDomain($x)
        );
        $attr->dwsTypes()->saveMany($types);

        foreach ($entity->grants as $key => $domainGrant) {
            $grant = DwsCertificationGrant::fromDomain($domainGrant, ['sort_order' => $key]);
            $attr->grants()->save($grant);
        }

        foreach ($entity->agreements as $key => $domainAgreement) {
            $agreement = DwsCertificationAgreement::fromDomain($domainAgreement, ['sort_order' => $key]);
            $attr->agreements()->save($agreement);
        }

        return $certification->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsCertificationAttr::whereIn('dws_certification_id', $ids)->delete();
        DwsCertification::destroy($ids);
    }
}
