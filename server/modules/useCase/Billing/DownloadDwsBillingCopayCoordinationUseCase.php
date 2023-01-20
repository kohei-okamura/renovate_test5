<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 利用者負担上限額管理結果票ダウンロードユースケース.
 */
interface DownloadDwsBillingCopayCoordinationUseCase
{
    /**
     * 利用者負担上限額管理結果票をダウンロードする.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $copayCoordinationId
     * @return array
     */
    public function handle(Context $context, int $billingId, int $bundleId, int $copayCoordinationId): array;
}
