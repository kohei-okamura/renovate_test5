<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Polite;

/**
 * 介護保険サービス：予実：超過単位.
 */
final class LtcsProvisionReportOverScore extends Polite
{
    /**
     * {@link \Domain\ProvisionReport\LtcsProvisionReportOverScore} constructor.
     *
     * @param int $maxBenefitExcessScore 区分支給限度基準を超える単位数
     * @param int $maxBenefitQuotaExcessScore 種類支給限度基準を超える単位数
     */
    public function __construct(
        public readonly int $maxBenefitExcessScore,
        public readonly int $maxBenefitQuotaExcessScore
    ) {
    }

    /**
     * 2つの「支給限度基準を超える単位数」の合計を返す.
     *
     * @return int
     */
    public function sum(): int
    {
        return $this->maxBenefitExcessScore + $this->maxBenefitQuotaExcessScore;
    }
}
