<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingStatus;
use Domain\Context\Context;

/**
 * 介護保険サービス：明細書 状態一括更新ユースケース.
 */
interface BulkUpdateLtcsBillingStatementStatusUseCase
{
    /**
     * 介護保険サービス：明細書 状態を一括更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param array $ids
     * @param \Domain\Billing\LtcsBillingStatus $status
     * @return void
     */
    public function handle(Context $context, int $billingId, int $bundleId, array $ids, LtcsBillingStatus $status): void;
}
