<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Context\Context;

/**
 * 介護保険サービス：請求：明細書状態確認ユースケース.
 */
interface ConfirmLtcsBillingStatementStatusUseCase
{
    /**
     * 介護保険サービス：請求：明細書状態確認.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     */
    public function handle(Context $context, LtcsBilling $billing): void;
}
