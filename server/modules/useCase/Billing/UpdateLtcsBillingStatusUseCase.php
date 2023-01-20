<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Closure;
use Domain\Billing\LtcsBillingStatus;
use Domain\Context\Context;

/**
 * 介護保険サービス：請求状態更新ユースケース.
 */
interface UpdateLtcsBillingStatusUseCase
{
    /**
     * 介護保険サービス：請求状態を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param \Domain\Billing\LtcsBillingStatus $status
     * @param \Closure $dispatchClosure JOB Dispatch の Closure
     * @return array JSONレスポンス用の値
     */
    public function handle(Context $context, int $id, LtcsBillingStatus $status, Closure $dispatchClosure): array;
}
