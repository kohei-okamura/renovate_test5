<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;

/**
 * 障害福祉サービス：明細書：上限管理結果編集ユースケース.
 */
interface EditDwsBillingStatementCopayCoordinationUseCase
{
    /**
     * 障害福祉サービス：明細書の「上限管理結果」を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \Domain\Billing\CopayCoordinationResult $result
     * @param int $amount
     * @return \Domain\Billing\DwsBillingStatement
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        CopayCoordinationResult $result,
        int $amount
    ): DwsBillingStatement;
}
