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
 * 障害福祉サービス請求：サービス単位（居宅介護） リポジトリ interface.
 */
interface DwsHomeHelpServiceChunkRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsHomeHelpServiceChunk[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $entity
     * @return \Domain\Billing\DwsHomeHelpServiceChunk
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
