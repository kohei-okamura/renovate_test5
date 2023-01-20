<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 障害福祉サービス：請求：ファイル取得 ユースケース.
 */
interface GetDwsBillingFileInfoUseCase
{
    /**
     * 障害福祉サービス：請求：ファイルを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param string $token
     * @return string URL(AWSの想定)
     */
    public function handle(Context $context, int $billingId, string $token): string;
}
