<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\Certification;
use Domain\Staff\Staff as DomainStaff;
use Domain\Staff\StaffRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * StaffRepository eloquent implementation.
 */
final class StaffRepositoryEloquentImpl extends EloquentRepository implements StaffRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Staff::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Staff $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainStaff
    {
        assert($entity instanceof DomainStaff);

        $staff = Staff::fromDomain($entity)->saveIfNotExists();
        $attr = StaffAttr::fromDomain($entity);

        $this->storeStaffAttr($staff, $attr);
        $this->storeCertifications($entity, $attr);
        $this->storeRoles($entity, $attr);
        $this->storeOffices($entity, $attr);
        $this->storeOfficeGroups($entity, $attr);

        return $staff->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        StaffAttr::whereIn('staff_id', $ids)->delete();
        StaffEmailVerification::whereIn('staff_id', $ids)->delete();
        StaffPasswordReset::whereIn('staff_id', $ids)->delete();
        StaffRememberToken::whereIn('staff_id', $ids)->delete();
        Staff::destroy($ids);
    }

    /**
     * スタッフ属性情報をデータベースに保管する.
     *
     * @param \Infrastructure\Staff\Staff $staff
     * @param \Infrastructure\Staff\StaffAttr $attr
     * @return void
     */
    private function storeStaffAttr(Staff $staff, StaffAttr $attr): void
    {
        $staff->attr()->save($attr);
    }

    /**
     * 資格情報をデータベースに保管する.
     *
     * @param \Domain\Staff\Staff $entity
     * @param \Infrastructure\Staff\StaffAttr $attr
     * @return void
     */
    private function storeCertifications(DomainStaff $entity, StaffAttr $attr): void
    {
        $xs = Seq::fromArray($entity->certifications)->map(
            fn (Certification $x) => StaffAttrCertification::fromDomain($x)
        );
        $attr->certifications()->saveMany($xs);
    }

    /**
     * ロール情報をデータベースに保管する.
     *
     * @param \Domain\Staff\Staff $entity
     * @param \Infrastructure\Staff\StaffAttr $attr
     * @return void
     */
    private function storeRoles(DomainStaff $entity, StaffAttr $attr): void
    {
        $attr->roles()->sync($entity->roleIds);
    }

    /**
     * 事業所情報をデータベースに保管する.
     *
     * @param \Domain\Staff\Staff $entity
     * @param \Infrastructure\Staff\StaffAttr $attr
     * @return void
     */
    private function storeOffices(DomainStaff $entity, StaffAttr $attr): void
    {
        $attr->offices()->sync($entity->officeIds);
    }

    /**
     * 事業所グループ情報をデータベースに保管する.
     *
     * @param \Domain\Staff\Staff $entity
     * @param \Infrastructure\Staff\StaffAttr $attr
     * @return void
     */
    private function storeOfficeGroups(DomainStaff $entity, StaffAttr $attr): void
    {
        $attr->officeGroups()->sync($entity->officeGroupIds);
    }
}
