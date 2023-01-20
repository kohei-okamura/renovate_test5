<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Context\Context;

/**
 * 障害福祉サービス：明細書：上限管理区分 更新ユースケース.
 */
interface UpdateDwsBillingStatementCopayCoordinationStatusUseCase
{
    /**
     * 障害福祉サービス 明細書を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @param \Domain\Billing\DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus
     * @return array
     */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id,
        DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus
    ): array;
}
