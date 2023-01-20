<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;

/**
 * 障害福祉サービス：請求編集ユースケース.
 */
interface EditDwsBillingUseCase
{
    /**
     * 障害福祉サービス：請求編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return \Domain\Billing\DwsBilling
     */
    public function handle(Context $context, int $id, array $values): DwsBilling;
}
