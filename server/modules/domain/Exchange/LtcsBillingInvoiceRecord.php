<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingInvoice;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;

/**
 * 介護保険サービス：伝送：データレコード：介護給付費請求書.
 */
final class LtcsBillingInvoiceRecord extends LtcsDataRecord
{
    /** @var int 保険・公費等区分コード：保険請求 */
    public const INVOICE_TYPE_INSURANCE = 1;

    /** @var int 保険・公費等区分コード：公費請求 */
    public const INVOICE_TYPE_SUBSIDY = 2;

    /** @var int 法別番号：保険者請求分 */
    public const DEFRAYER_CATEGORY_INSURANCE = 0;

    /** @var string 請求情報区分コード：居宅サービス・施設サービス・介護予防サービス・地域密着型サービス */
    public const BILLING_CATEGORY_SERVICE_PROVISION = '01';

    // /** @var string 請求情報区分コード：居宅介護支援・介護予防支援 */
    // public const BILLING_CATEGORY_CARE_PLANNING = '02';

    /** @var string 請求情報区分コード：指定なし */
    public const BILLING_CATEGORY_NONE = '0';

    /**
     * {@link \Domain\Exchange\LtcsBillingInvoiceRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $officeCode 事業所番号
     * @param int $invoiceType 保険・公費等区分コード
     * @param null|\Domain\Common\DefrayerCategory $defrayerCategory 法別番号
     * @param string $billingCategory 請求情報区分コード
     * @param int $statementCount サービス費用：件数
     * @param int $totalScore サービス費用：単位数
     * @param int $totalFee サービス費用：費用合計
     * @param int $insuranceAmount サービス費用：保険請求額
     * @param int $subsidyAmount サービス費用：公費請求額
     * @param int $copayAmount サービス費用：利用者負担
     */
    public function __construct(
        #[JsonIgnore] public readonly Carbon $providedIn,
        #[JsonIgnore] public readonly string $officeCode,
        #[JsonIgnore] public readonly int $invoiceType,
        #[JsonIgnore] public readonly ?DefrayerCategory $defrayerCategory,
        #[JsonIgnore] public readonly string $billingCategory,
        #[JsonIgnore] public readonly int $statementCount,
        #[JsonIgnore] public readonly int $totalScore,
        #[JsonIgnore] public readonly int $totalFee,
        #[JsonIgnore] public readonly int $insuranceAmount,
        #[JsonIgnore] public readonly int $subsidyAmount,
        #[JsonIgnore] public readonly int $copayAmount
    ) {
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingInvoice $invoice
     * @param int $statementCount
     * @return self
     */
    public static function from(
        LtcsBilling $billing,
        LtcsBillingBundle $bundle,
        LtcsBillingInvoice $invoice,
        int $statementCount
    ): self {
        $isInsurance = !$invoice->isSubsidy;
        $defrayerCategory = $invoice->defrayerCategory;
        return new self(
            providedIn: $bundle->providedIn,
            officeCode: $billing->office->code,
            invoiceType: $isInsurance
                ? self::INVOICE_TYPE_INSURANCE
                : self::INVOICE_TYPE_SUBSIDY,
            defrayerCategory: $defrayerCategory,
            billingCategory: $isInsurance || $defrayerCategory === DefrayerCategory::livelihoodProtection()
                ? self::BILLING_CATEGORY_SERVICE_PROVISION
                : self::BILLING_CATEGORY_NONE,
            statementCount: $statementCount,
            totalScore: $invoice->totalScore,
            totalFee: $invoice->totalFee,
            insuranceAmount: $invoice->insuranceAmount,
            subsidyAmount: $invoice->subsidyAmount,
            copayAmount: $invoice->copayAmount,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // 交換情報識別番号
            self::RECORD_CATEGORY_LTCS_BILLING_STATEMENT,
            // サービス提供年月
            self::formatYearMonth($this->providedIn),
            // 事業所番号
            $this->officeCode,
            // 保険・公費等区分コード
            $this->invoiceType,
            // 法別番号
            $this->defrayerCategory ? $this->defrayerCategory->value() : self::DEFRAYER_CATEGORY_INSURANCE,
            // 請求情報区分コード
            $this->billingCategory,
            // サービス費用：件数
            $this->statementCount,
            // サービス費用：単位数
            $this->totalScore,
            // サービス費用：費用合計
            $this->totalFee,
            // サービス費用：保険請求額
            $this->insuranceAmount,
            // サービス費用：公費請求額
            $this->subsidyAmount,
            // サービス費用：利用者負担
            $this->copayAmount,
            // 特定入所者介護サービス費等：件数
            self::UNUSED,
            // 特定入所者介護サービス費等：延べ日数
            self::UNUSED,
            // 特定入所者介護サービス費等：費用合計
            self::UNUSED,
            // 特定入所者介護サービス費等：利用者負担
            self::UNUSED,
            // 特定入所者介護サービス費等：公費請求額
            self::UNUSED,
            // 特定入所者介護サービス費等：保険請求額
            self::UNUSED,
        ];
    }
}
