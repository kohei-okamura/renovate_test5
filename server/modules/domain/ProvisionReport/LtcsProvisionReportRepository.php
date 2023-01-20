<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * LtcsProvisionReport Repository Interface.
 */
interface LtcsProvisionReportRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $entity
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
