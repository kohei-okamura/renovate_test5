<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Domain\Office\OfficeQualification;
use Infrastructure\Office\Office;
use Infrastructure\Office\OfficeAttr;
use Infrastructure\Office\OfficeAttrQualification;
use Infrastructure\Office\OfficeDwsCommAccompanyService;
use Infrastructure\Office\OfficeDwsGenericService;
use Infrastructure\Office\OfficeLtcsCareManagementService;
use Infrastructure\Office\OfficeLtcsCompHomeVisitingService;
use Infrastructure\Office\OfficeLtcsHomeVisitLongTermCareService;
use Infrastructure\Office\OfficeLtcsPreventionService;
use ScalikePHP\Seq;

/**
 * Office fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait OfficeFixture
{
    /**
     * 事業所登録.
     *
     * @return void
     */
    protected function createOffices(): void
    {
        foreach ($this->examples->offices as $entity) {
            $office = Office::fromDomain($entity)->saveIfNotExists();
            $office->attr()->save(OfficeAttr::fromDomain($entity));
            $qualifications = Seq::fromArray($entity->qualifications)
                ->map(fn (OfficeQualification $x) => OfficeAttrQualification::fromDomain($x));
            $attr = $office->attr;
            if ($entity->dwsGenericService !== null) {
                $attr->dwsGenericService()->save(
                    OfficeDwsGenericService::fromDomain($entity->dwsGenericService)
                );
            }
            if ($entity->dwsCommAccompanyService !== null) {
                $attr->dwsCommAccompanyService()->save(
                    OfficeDwsCommAccompanyService::fromDomain($entity->dwsCommAccompanyService)
                );
            }
            if ($entity->ltcsCareManagementService !== null) {
                $attr->ltcsCareManagementService()->save(
                    OfficeLtcsCareManagementService::fromDomain($entity->ltcsCareManagementService)
                );
            }
            if ($entity->ltcsHomeVisitLongTermCareService !== null) {
                $attr->ltcsHomeVisitLongTermCareService()->save(
                    OfficeLtcsHomeVisitLongTermCareService::fromDomain($entity->ltcsHomeVisitLongTermCareService)
                );
            }
            if ($entity->ltcsCompHomeVisitingService !== null) {
                $attr->ltcsCompHomeVisitingService()->save(
                    OfficeLtcsCompHomeVisitingService::fromDomain($entity->ltcsCompHomeVisitingService)
                );
            }
            if ($entity->ltcsPreventionService !== null) {
                $attr->ltcsPreventionService()->save(
                    OfficeLtcsPreventionService::fromDomain($entity->ltcsPreventionService)
                );
            }
            $attr->qualifications()->saveMany($qualifications);
        }
    }
}
