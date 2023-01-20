<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Closure;
use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;

/**
 * 障害福祉サービス：請求 状態更新ユースケース.
 */
interface UpdateDwsBillingStatusUseCase
{
    /**
     * 障害福祉サービス：請求 状態を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param \Domain\Billing\DwsBillingStatus $status
     * @param \Closure $dispatchClosure JOB DispatchのClosure
     * @return array Response Json用array
     */
    public function handle(Context $context, int $id, DwsBillingStatus $status, Closure $dispatchClosure): array;
}
