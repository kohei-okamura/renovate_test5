<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Billing\LtcsBilling;
use Domain\Context\Context;
use UseCase\Billing\ConfirmLtcsBillingStatementStatusUseCase;

/**
 * 介護保険サービス：請求：明細書 状態確認ジョブ.
 */
final class ConfirmLtcsBillingStatementStatusJob extends Job
{
    private Context $context;
    private LtcsBilling $billing;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     */
    public function __construct(Context $context, LtcsBilling $billing)
    {
        $this->context = $context;
        $this->billing = $billing;
    }

    /**
     * 介護保険サービス：請求：明細書 状態確認ジョブを実行する.
     *
     * @param \UseCase\Billing\ConfirmLtcsBillingStatementStatusUseCase $useCase
     * @return void
     */
    public function handle(ConfirmLtcsBillingStatementStatusUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->billing);
    }
}
