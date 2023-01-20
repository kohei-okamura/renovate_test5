<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use Domain\Pdf\PdfSupport;
use Domain\Polite;

/**
 * 障害福祉サービス：請求書 PDF.
 */
final class DwsBillingInvoicePdf extends Polite
{
    use PdfSupport;

    /**
     * {@link \Domain\Billing\DwsBillingInvoicePdf} constructor.
     *
     * @param string $destinationName 宛名（請求先）
     * @param \Domain\Billing\DwsBillingOffice $office 事業所
     * @param array|string[] $providedIn サービス提供年月
     * @param array|string[] $claimAmount 請求金額(右詰め)
     * @param array|\Domain\Billing\DwsBillingInvoiceItem[] $items 明細
     * @param \Domain\Billing\DwsBillingPayment $dwsPayment 小計
     * @param \Domain\Billing\DwsBillingHighCostPayment $highCostDwsPayment 特定障害者特別給付費
     * @param int $totalCount 合計：件数
     * @param int $totalScore 合計：単位数
     * @param int $totalFee 合計：費用合計
     * @param int $totalBenefit 合計：給付費請求額
     * @param int $totalCopay 合計：利用者負担額
     * @param int $totalSubsidy 合計：自治体助成額
     * @param string $issuedOn 発行日
     */
    public function __construct(
        public readonly string $destinationName,
        public readonly DwsBillingOffice $office,
        public readonly array $providedIn,
        public readonly array $claimAmount,
        public readonly array $items,
        public readonly DwsBillingPayment $dwsPayment,
        public readonly DwsBillingHighCostPayment $highCostDwsPayment,
        public readonly int $totalCount,
        public readonly int $totalScore,
        public readonly int $totalFee,
        public readonly int $totalBenefit,
        public readonly int $totalCopay,
        public readonly int $totalSubsidy,
        public readonly string $issuedOn
    ) {
    }

    /**
     * 障害福祉サービス：請求書 PDF ドメインモデルを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingInvoice $invoice
     * @return static
     */
    public static function from(DwsBilling $billing, DwsBillingBundle $bundle, DwsBillingInvoice $invoice): self
    {
        return new self(
            destinationName: $bundle->cityName . '長',
            office: $billing->office,
            providedIn: self::localized($bundle->providedIn),
            claimAmount: preg_split('//u', sprintf('% 9d', $invoice->claimAmount)),
            items: $invoice->items,
            dwsPayment: $invoice->dwsPayment,
            highCostDwsPayment: $invoice->highCostDwsPayment,
            totalCount: $invoice->totalCount,
            totalScore: $invoice->totalScore,
            totalFee: $invoice->totalFee,
            totalBenefit: $invoice->totalBenefit,
            totalCopay: $invoice->totalCopay,
            totalSubsidy: $invoice->totalSubsidy,
            issuedOn: Carbon::now()->toJapaneseDate(),
        );
    }
}
