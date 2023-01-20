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
 * 障害福祉サービス：請求：状態確認ユースケース.
 */
interface ConfirmDwsBillingStatusUseCase
{
    /**
     * 障害福祉サービス：請求：状態確認.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     */
    public function handle(Context $context, DwsBilling $billing): void;
}
