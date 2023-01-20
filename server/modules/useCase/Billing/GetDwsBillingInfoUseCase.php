<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 障害福祉サービス：請求取得ユースケース.
 */
interface GetDwsBillingInfoUseCase
{
    /**
     * 請求書情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id 請求書ID
     * @return array JSON変換可能なレスポンスデータ
     */
    public function handle(Context $context, int $id): array;
}
