<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Context\Context;
use Domain\ProvisionReport\DwsProvisionReport;

/**
 * 障害福祉サービス：予実 状態更新ユースケース.
 */
interface UpdateDwsProvisionReportStatusUseCase
{
    /**
     * 障害福祉サービス：予実 状態を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param array $values
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        string $providedIn,
        array $values
    ): DwsProvisionReport;
}
