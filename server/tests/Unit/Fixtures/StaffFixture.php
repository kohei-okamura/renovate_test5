<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Domain\Staff\Certification;
use Infrastructure\Staff\Staff;
use Infrastructure\Staff\StaffAttr;
use Infrastructure\Staff\StaffAttrCertification;
use ScalikePHP\Seq;

/**
 * Staff fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait StaffFixture
{
    /**
     * スタッフ 登録.
     *
     * @return void
     */
    protected function createStaffs(): void
    {
        foreach ($this->examples->staffs as $entity) {
            $staff = Staff::fromDomain($entity)->saveIfNotExists();
            $attr = StaffAttr::fromDomain($entity);
            $staff->attr()->save($attr);
            $attr->offices()->sync($entity->officeIds);
            $attr->officeGroups()->sync($entity->officeGroupIds);
            $attr->roles()->sync($entity->roleIds);

            $xs = Seq::fromArray($entity->certifications)->map(
                fn (Certification $x) => StaffAttrCertification::fromDomain($x)
            );
            $attr->certifications()->saveMany($xs);
        }
    }
}
