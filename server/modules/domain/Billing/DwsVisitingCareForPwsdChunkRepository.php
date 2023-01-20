<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス単位（重度訪問介護） リポジトリ interface.
 */
interface DwsVisitingCareForPwsdChunkRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingFile $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
