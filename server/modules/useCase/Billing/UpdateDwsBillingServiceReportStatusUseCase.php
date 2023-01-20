<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * サービス影響実績記録表状態更新ユースケース.
 */
interface UpdateDwsBillingServiceReportStatusUseCase
{
    /**
     * 障害福祉サービス：サービス実績記録票を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param array $values
     * @param callable $f
     * @return array JSON戻り値
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        array $values
    ): array;
}
