<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 介護保険サービス：明細書取得ユースケース.
 */
interface GetLtcsBillingStatementInfoUseCase
{
    /**
     * 介護保険サービス：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @return array JSON変換可能なレスポンスデータ
     */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id
    ): array;
}
