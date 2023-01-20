<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingStatus;
use Domain\Context\Context;

/**
 * 介護保険サービス：明細書状態更新ユースケース.
 */
interface UpdateLtcsBillingStatementStatusUseCase
{
    /**
     * 介護保険サービス：明細書状態を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @param \Domain\Billing\LtcsBillingStatus $status
     * @param callable $f
     * @throws \Throwable
     * @return array JSONレスポンス用の値
     */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id,
        LtcsBillingStatus $status,
        callable $f
    ): array;
}
