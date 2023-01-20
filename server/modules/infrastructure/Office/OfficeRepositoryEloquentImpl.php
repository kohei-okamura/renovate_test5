<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\Office as DomainOffice;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * OfficeRepository eloquent implementation.
 */
final class OfficeRepositoryEloquentImpl extends EloquentRepository implements OfficeRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Office::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Office $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainOffice
    {
        assert($entity instanceof DomainOffice);

        $office = Office::fromDomain($entity)->saveIfNotExists();
        $attr = OfficeAttr::fromDomain($entity);

        $this->storeOfficeAttr($office, $attr);
        if ($entity->dwsGenericService !== null) {
            $this->storeDwsGenericService($entity, $attr);
        }
        if ($entity->dwsCommAccompanyService !== null) {
            $this->storeDwsCommAccompanyService($entity, $attr);
        }
        if ($entity->ltcsCareManagementService !== null) {
            $this->storeLtcsCareManagementService($entity, $attr);
        }
        if ($entity->ltcsHomeVisitLongTermCareService !== null) {
            $this->storeLtcsHomeVisitLongTermCareService($entity, $attr);
        }
        if ($entity->ltcsCompHomeVisitingService !== null) {
            $this->storeLtcsCompHomeVisitingService($entity, $attr);
        }
        if ($entity->ltcsPreventionService !== null) {
            $this->storeLtcsPreventionService($entity, $attr);
        }
        if (empty($entity->qualifications) === false) {
            $this->storeQualifications($entity, $attr);
        }

        return $office->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        OfficeAttr::whereIn('office_id', $ids)->delete();
        Office::destroy($ids);
    }

    /**
     * 事業所属性情報をデータベースに保管する.
     *
     * @param \Infrastructure\Office\Office $office
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeOfficeAttr(Office $office, OfficeAttr $attr): void
    {
        $office->attr()->save($attr);
    }

    /**
     * 事業所：障害福祉サービスをデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeDwsGenericService(DomainOffice $entity, OfficeAttr $attr): void
    {
        $x = OfficeDwsGenericService::fromDomain($entity->dwsGenericService);
        $attr->dwsGenericService()->save($x);
    }

    /**
     * 事業所：障害福祉サービス（地域生活支援事業・移動支援）をデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeDwsCommAccompanyService(DomainOffice $entity, OfficeAttr $attr): void
    {
        $x = OfficeDwsCommAccompanyService::fromDomain($entity->dwsCommAccompanyService);
        $attr->dwsCommAccompanyService()->save($x);
    }

    /**
     * 事業所：介護保険サービス：居宅介護支援をデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeLtcsCareManagementService(DomainOffice $entity, OfficeAttr $attr): void
    {
        $x = OfficeLtcsCareManagementService::fromDomain($entity->ltcsCareManagementService);
        $attr->ltcsCareManagementService()->save($x);
    }

    /**
     * 事業所：介護保険サービス：訪問介護をデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeLtcsHomeVisitLongTermCareService(DomainOffice $entity, OfficeAttr $attr): void
    {
        $x = OfficeLtcsHomeVisitLongTermCareService::fromDomain($entity->ltcsHomeVisitLongTermCareService);
        $attr->ltcsHomeVisitLongTermCareService()->save($x);
    }

    /**
     * 事業所：介護保険サービス：訪問型サービス（総合事業）をデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeLtcsCompHomeVisitingService(DomainOffice $entity, OfficeAttr $attr): void
    {
        $x = OfficeLtcsCompHomeVisitingService::fromDomain($entity->ltcsCompHomeVisitingService);
        $attr->ltcsCompHomeVisitingService()->save($x);
    }

    /**
     * 事業所：介護保険サービス：介護予防支援をデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeLtcsPreventionService(DomainOffice $entity, OfficeAttr $attr): void
    {
        $x = OfficeLtcsPreventionService::fromDomain($entity->ltcsPreventionService);
        $attr->ltcsPreventionService()->save($x);
    }

    /**
     * 事業所：指定区分をデータベースに保管する.
     *
     * @param \Domain\Office\Office $entity
     * @param \Infrastructure\Office\OfficeAttr $attr
     * @return void
     */
    private function storeQualifications(DomainOffice $entity, OfficeAttr $attr): void
    {
        $xs = Seq::fromArray($entity->qualifications)->map(
            fn (OfficeQualification $x) => OfficeAttrQualification::fromDomain($x)
        );
        $attr->qualifications()->saveMany($xs);
    }
}
