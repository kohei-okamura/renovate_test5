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
 * 障害福祉サービス：明細書特定ユースケース.
 */
interface IdentifyDwsBillingStatementUseCase
{
    /**
     * 請求・請求単位・利用者を指定して明細書を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $userId
     * @return \Domain\Billing\DwsBillingStatement|\ScalikePHP\Option
     */
    public function handle(Context $context, int $dwsBillingId, int $dwsBillingBundleId, int $userId): Option;
}
