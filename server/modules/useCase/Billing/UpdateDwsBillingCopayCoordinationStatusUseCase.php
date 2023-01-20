<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;

/**
 * 障害福祉サービス利用者負担上限額管理結果票状態更新ユースケース.
 */
interface UpdateDwsBillingCopayCoordinationStatusUseCase
{
    /**
     * 障害福祉サービス利用者負担上限額管理結果票状態を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \Domain\Billing\DwsBillingStatus $status
     * @return array JSONレスポンス用の値
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        DwsBillingStatus $status
    ): array;
}
