<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;
use UseCase\Billing\ConfirmDwsBillingStatusUseCase;

/**
 * 障害福祉サービス：請求 状態確認ジョブ.
 */
final class ConfirmDwsBillingStatusJob extends Job
{
    private Context $context;
    private DwsBilling $billing;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     */
    public function __construct(Context $context, DwsBilling $billing)
    {
        $this->context = $context;
        $this->billing = $billing;
    }

    /**
     * 障害福祉サービス：請求 状態確認ジョブを実行する.
     *
     * @param \UseCase\Billing\ConfirmDwsBillingStatusUseCase $useCase
     * @return void
     */
    public function handle(ConfirmDwsBillingStatusUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->billing);
    }
}
