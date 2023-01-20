<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 障害：サービス提供実績記録票 PDF 明細算定時間
 */
final class DwsBillingServiceReportPdfDuration extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingServiceReportPdfDuration} constructor.
     *
     * @param string $start 開始時間
     * @param string $end 終了時間
     * @param string $serviceDurationHours 算定時間数
     * @param string $movingDurationHours 移動時間数
     */
    public function __construct(
        public readonly string $start,
        public readonly string $end,
        public readonly string $serviceDurationHours,
        public readonly string $movingDurationHours,
    ) {
    }

    /**
     * すべて空文字の明細算定時間モデルを返す.
     *
     * @return static
     */
    public static function empty(): self
    {
        return new self(
            start: '',
            end: '',
            serviceDurationHours: '',
            movingDurationHours: ''
        );
    }
}
