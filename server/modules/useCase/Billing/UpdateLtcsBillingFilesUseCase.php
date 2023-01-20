<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Context\Context;

/**
 * 介護保険サービス：請求：ファイル更新ユースケース.
 */
interface UpdateLtcsBillingFilesUseCase
{
    /**
     * 介護保険サービス：請求：ファイル更新 実装.
     *
     * @param \Domain\Context\Context $context
     * @param int $id 請求ID
     * @return \Domain\Billing\LtcsBilling
     */
    public function handle(Context $context, int $id): LtcsBilling;
}
