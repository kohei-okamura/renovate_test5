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
use Domain\Billing\DwsBillingInvoiceItem;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;

/**
 * 障害：介護給付費等請求書：明細情報レコード.
 */
final class DwsBillingInvoiceItemRecord extends DwsBillingInvoiceRecord
{
    /**
     * {@link \Domain\Exchange\DwsBillingInvoiceItemRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param \Domain\Billing\DwsBillingPaymentCategory $paymentCategory 給付種別
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode サービス種類コード
     * @param int $subtotalCount 件数
     * @param null|int $subtotalScore 単位数
     * @param int $subtotalFee 費用合計
     * @param int $subtotalBenefit 給付費請求額
     * @param null|int $subtotalCopay 利用者負担額
     * @param null|int $subtotalSubsidy 自治体助成額
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        #[JsonIgnore] public readonly DwsBillingPaymentCategory $paymentCategory,
        #[JsonIgnore] public readonly DwsServiceDivisionCode $serviceDivisionCode,
        #[JsonIgnore] public readonly int $subtotalCount,
        #[JsonIgnore] public readonly ?int $subtotalScore,
        #[JsonIgnore] public readonly int $subtotalFee,
        #[JsonIgnore] public readonly int $subtotalBenefit,
        #[JsonIgnore] public readonly ?int $subtotalCopay,
        #[JsonIgnore] public readonly ?int $subtotalSubsidy
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_ITEM,
            providedIn: $providedIn,
            cityCode: $cityCode,
            officeCode: $officeCode
        );
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingInvoiceItem $invoiceItem
     * @return static
     */
    public static function from(DwsBilling $billing, DwsBillingBundle $bundle, DwsBillingInvoiceItem $invoiceItem): self
    {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            paymentCategory: $invoiceItem->paymentCategory,
            serviceDivisionCode: $invoiceItem->serviceDivisionCode,
            subtotalCount: $invoiceItem->subtotalCount,
            subtotalScore: $invoiceItem->subtotalScore,
            subtotalFee: $invoiceItem->subtotalFee,
            subtotalBenefit: $invoiceItem->subtotalBenefit,
            subtotalCopay: $invoiceItem->subtotalCopay,
            subtotalSubsidy: $invoiceItem->subtotalSubsidy,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->paymentCategory->value(),
            $this->serviceDivisionCode->value(),
            $this->subtotalCount,
            $this->subtotalScore ?? '',
            $this->subtotalFee,
            $this->subtotalBenefit,
            self::UNUSED,
            $this->subtotalCopay ?? '',
            $this->subtotalSubsidy ?? '',
        ];
    }
}
