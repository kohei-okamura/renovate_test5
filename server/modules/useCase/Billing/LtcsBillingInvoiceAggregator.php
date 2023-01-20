<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

/**
 * 介護保険サービス：請求書生成時向け集計インターフェース.
 *
 * @property-read int $statementCount 明細書の枚数
 * @property-read int $totalScore 単位数
 * @property-read int $claimAmount 請求額
 * @property-read int $copayAmount 利用者負担額
 */
interface LtcsBillingInvoiceAggregator
{
    /**
     * 単位数, 請求額, 利用者負担額を加える.
     *
     * @param int $totalScore
     * @param int $claimAmount
     * @param int $copayAmount
     * @return $this
     */
    public function append(int $totalScore, int $claimAmount, int $copayAmount): self;
}
