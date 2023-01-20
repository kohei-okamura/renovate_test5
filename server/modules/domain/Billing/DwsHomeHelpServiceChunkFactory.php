<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）Factory.
 */
interface DwsHomeHelpServiceChunkFactory
{
    /**
     * 障害福祉サービス請求：サービス単位（居宅介護） を構築する.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param \Domain\ProvisionReport\DwsProvisionReportItem $item
     * @return \Domain\Billing\DwsHomeHelpServiceChunk
     */
    public function factory(DwsProvisionReport $report, DwsProvisionReportItem $item): DwsHomeHelpServiceChunk;
}
