<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingHighCostPayment;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingPayment;
use Domain\Common\Carbon;

/**
 * 障害：介護給付費等請求書：基本情報レコード.
 */
final class DwsBillingInvoiceSummaryRecord extends DwsBillingInvoiceRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingInvoiceSummaryRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param int $claimAmount 請求金額
     * @param \Domain\Billing\DwsBillingPayment $dwsPayment 小計：介護給付費等・特別介護給付費等
     * @param \Domain\Billing\DwsBillingHighCostPayment $highCostDwsPayment 小計：特定障害者特別給付費・高額障害福祉サービス費
     * @param int $totalCount 合計：件数
     * @param int $totalScore 合計：単位数
     * @param int $totalFee 合計：費用合計
     * @param int $totalBenefit 合計：給付費請求額
     * @param int $totalCopay 合計：利用者負担額
     * @param int $totalSubsidy 合計：自治体助成額
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        #[JsonIgnore] public readonly int $claimAmount,
        #[JsonIgnore] public readonly DwsBillingPayment $dwsPayment,
        #[JsonIgnore] public readonly DwsBillingHighCostPayment $highCostDwsPayment,
        #[JsonIgnore] public readonly int $totalCount,
        #[JsonIgnore] public readonly int $totalScore,
        #[JsonIgnore] public readonly int $totalFee,
        #[JsonIgnore] public readonly int $totalBenefit,
        #[JsonIgnore] public readonly int $totalCopay,
        #[JsonIgnore] public readonly int $totalSubsidy
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_SUMMARY,
            providedIn: $providedIn,
            cityCode: $cityCode,
            officeCode: $officeCode
        );
    }

    /**
     * インスタンス生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingInvoice $invoice
     * @return static
     */
    public static function from(DwsBilling $billing, DwsBillingBundle $bundle, DwsBillingInvoice $invoice): self
    {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            claimAmount: $invoice->claimAmount,
            dwsPayment: $invoice->dwsPayment,
            highCostDwsPayment: $invoice->highCostDwsPayment,
            totalCount: $invoice->totalCount,
            totalScore: $invoice->totalScore,
            totalFee: $invoice->totalFee,
            totalBenefit: $invoice->totalBenefit,
            totalCopay: $invoice->totalCopay,
            totalSubsidy: $invoice->totalSubsidy,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->claimAmount,
            $this->dwsPayment->subtotalDetailCount,
            $this->dwsPayment->subtotalScore,
            $this->dwsPayment->subtotalFee,
            $this->dwsPayment->subtotalBenefit,
            self::UNUSED,
            $this->dwsPayment->subtotalCopay,
            $this->dwsPayment->subtotalSubsidy,
            $this->highCostDwsPayment->subtotalDetailCount,
            $this->highCostDwsPayment->subtotalFee,
            $this->highCostDwsPayment->subtotalBenefit,
            $this->totalCount,
            $this->totalScore,
            $this->totalFee,
            $this->totalBenefit,
            self::UNUSED,
            $this->totalCopay,
            $this->totalSubsidy,
        ];
    }
}
