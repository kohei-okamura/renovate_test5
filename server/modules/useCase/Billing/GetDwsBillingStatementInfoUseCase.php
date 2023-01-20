<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 障害福祉サービス：明細書取得ユースケース.
 */
interface GetDwsBillingStatementInfoUseCase
{
    /**
     * 障害福祉サービス：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $dwsBillingStatementId
     * @return array JSON変換可能なレスポンスデータ
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $dwsBillingStatementId
    ): array;
}
