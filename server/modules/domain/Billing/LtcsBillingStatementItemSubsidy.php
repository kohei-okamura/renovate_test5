<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 介護保険サービス：明細書：明細：公費.
 */
final class LtcsBillingStatementItemSubsidy extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementItemSubsidy} constructor.
     *
     * @param int $count 日数・回数
     * @param int $totalScore サービス単位数
     */
    public function __construct(
        public readonly int $count,
        public readonly int $totalScore
    ) {
    }

    /**
     * 空を表すインスタンスを生成する.
     *
     * @return static
     */
    public static function empty(): self
    {
        return new self(
            count: 0,
            totalScore: 0,
        );
    }
}
