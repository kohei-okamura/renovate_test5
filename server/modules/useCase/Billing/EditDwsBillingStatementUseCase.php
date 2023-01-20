<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;

/**
 * 障害福祉サービス：明細書編集ユースケース.
 */
interface EditDwsBillingStatementUseCase
{
    /**
     * 障害福祉サービス：明細書を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param array $values
     * @return \Domain\Billing\DwsBillingStatement
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        array $values
    ): DwsBillingStatement;
}
