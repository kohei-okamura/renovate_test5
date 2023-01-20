<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書取得ユースケース.
 */
interface LookupLtcsBillingStatementUseCase
{
    /**
     * 介護保険サービス：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param \Domain\Billing\LtcsBilling|int $billing
     * @param \Domain\Billing\LtcsBillingBundle|int $bundle
     * @param int ...$ids
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, $billing, $bundle, int ...$ids): Seq;
}
