<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 利用者負担上限額管理結果更新ユースケース.
 */
interface UpdateDwsBillingStatementCopayCoordinationUseCase
{
    /**
     * 障害福祉サービスの明細書の「利用者負担上限額管理結果」を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $id
     * @param \ScalikePHP\Option $values 更新する copayCoordination の値(assoc)
     * @return array JSONレスポンス用の値
     */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $id,
        Option $values
    ): array;
}
