<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;

/**
 * 障害福祉サービス：請求コピーユースケース.
 */
interface CopyDwsBillingUseCase
{
    /**
     * 障害福祉サービス：請求をコピーして生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id 請求ID
     * @throws \Throwable
     * @return \Domain\Billing\DwsBilling
     */
    public function handle(Context $context, int $id): DwsBilling;
}
