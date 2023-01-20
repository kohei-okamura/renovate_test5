<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 介護保険サービス：明細書リフレッシュユースケース.
 */
interface RefreshLtcsBillingStatementUseCase
{
    /**
     * 介護保険サービス：明細書をリフレッシュする.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param array&int[] $statementIds
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, int $billingId, array $statementIds): void;
}
