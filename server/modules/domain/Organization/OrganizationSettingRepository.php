<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Organization;

use Domain\Repository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * OrganizationSetting Repository Interface.
 */
interface OrganizationSettingRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Organization\OrganizationSetting[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 事業者IDによるLookup.
     *
     * @param int ...$ids 事業者ID
     * @return \ScalikePHP\Map key=事業者ID value=Seq|OrganizationSetting[]
     */
    public function lookupByOrganizationId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Organization\OrganizationSetting $entity
     * @return \Domain\Organization\OrganizationSetting
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Organization\OrganizationSetting $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
