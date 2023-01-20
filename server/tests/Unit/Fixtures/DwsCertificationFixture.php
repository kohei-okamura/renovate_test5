<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Domain\DwsCertification\DwsType;
use Infrastructure\DwsCertification\DwsCertification;
use Infrastructure\DwsCertification\DwsCertificationAgreement;
use Infrastructure\DwsCertification\DwsCertificationAttr;
use Infrastructure\DwsCertification\DwsCertificationAttrDwsType;
use Infrastructure\DwsCertification\DwsCertificationGrant;
use ScalikePHP\Seq;

/**
 * DwsCertification fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsCertificationFixture
{
    /**
     * 障害福祉サービス受給者証 登録.
     *
     * @return void
     */
    protected function createDwsCertifications(): void
    {
        foreach ($this->examples->dwsCertifications as $entity) {
            $dwsCertification = DwsCertification::fromDomain($entity)->saveIfNotExists();
            $dwsCertification->attr()->save(DwsCertificationAttr::fromDomain($entity));

            $xs = Seq::fromArray($entity->dwsTypes)->map(
                fn (DwsType $x) => DwsCertificationAttrDwsType::fromDomain($x)
            );
            $dwsCertification->attr->dwsTypes()->saveMany($xs);
            foreach ($entity->grants as $key => $domainGrant) {
                $grant = DwsCertificationGrant::fromDomain($domainGrant, ['sort_order' => $key]);
                $dwsCertification->attr->grants()->save($grant);
            }
            foreach ($entity->agreements as $key => $domainAgreement) {
                $agreement = DwsCertificationAgreement::fromDomain($domainAgreement, ['sort_order' => $key]);
                $dwsCertification->attr->agreements()->save($agreement);
            }
        }
    }
}
