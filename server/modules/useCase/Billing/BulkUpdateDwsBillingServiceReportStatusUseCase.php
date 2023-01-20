<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;

/**
 * サービス提供実績記録票 状態一括更新ユースケース.
 */
interface BulkUpdateDwsBillingServiceReportStatusUseCase
{
    /**
     * サービス提供実績記録票 状態を一括更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param array $ids
     * @param \Domain\Billing\DwsBillingStatus $status
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, int $billingId, array $ids, DwsBillingStatus $status): void;
}
