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
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;

/**
 * 障害：介護給付費等明細書：集計情報レコード.
 */
final class DwsBillingStatementAggregateRecord extends DwsBillingStatementRecord
{
    private const UNIT_CONST_FRACTION_DIGITS = 3;

    /**
     * {@link \Domain\Exchange\DwsBillingStatementAggregateRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode サービス種類コード
     * @param int $serviceDays 請求額集計欄：サービス利用日数
     * @param int $subtotalScore 請求額集計欄：給付単位数
     * @param \Domain\Common\Decimal $unitCost 請求額集計欄：単位数単価
     * @param int $subtotalFee 請求額集計欄：総費用額
     * @param int $unmanagedCopay 請求額集計欄：利用者負担額（1割相当額）
     * @param int $managedCopay 請求額集計欄：利用者負担額
     * @param int $cappedCopay 請求額集計欄：上限月額調整
     * @param null|int $adjustedCopay 請求額集計欄：調整後利用者負担額
     * @param null|int $coordinatedCopay 請求額集計欄：上限額管理後利用者負担額
     * @param int $subtotalCopay 請求額集計欄：決定利用者負担額
     * @param int $subtotalBenefit 請求額集計欄：請求額：給付費
     * @param null|int $subtotalSubsidy 請求額集計欄：自治体助成分請求額
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        #[JsonIgnore] public readonly DwsServiceDivisionCode $serviceDivisionCode,
        #[JsonIgnore] public readonly int $serviceDays,
        #[JsonIgnore] public readonly int $subtotalScore,
        #[JsonIgnore] public readonly Decimal $unitCost,
        #[JsonIgnore] public readonly int $subtotalFee,
        #[JsonIgnore] public readonly int $unmanagedCopay,
        #[JsonIgnore] public readonly int $managedCopay,
        #[JsonIgnore] public readonly int $cappedCopay,
        #[JsonIgnore] public readonly ?int $adjustedCopay,
        #[JsonIgnore] public readonly ?int $coordinatedCopay,
        #[JsonIgnore] public readonly int $subtotalCopay,
        #[JsonIgnore] public readonly int $subtotalBenefit,
        #[JsonIgnore] public readonly ?int $subtotalSubsidy
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_AGGREGATE,
            providedIn: $providedIn,
            cityCode: $cityCode,
            officeCode: $officeCode,
            dwsNumber: $dwsNumber
        );
    }

    /**
     * インスタンス生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingUser $user
     * @param \Domain\Billing\DwsBillingStatementAggregate $aggregate
     * @return static
     */
    public static function from(
        DwsBilling $billing,
        DwsBillingBundle $bundle,
        DwsBillingUser $user,
        DwsBillingStatementAggregate $aggregate
    ): self {
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            dwsNumber: $user->dwsNumber,
            serviceDivisionCode: $aggregate->serviceDivisionCode,
            serviceDays: $aggregate->serviceDays,
            subtotalScore: $aggregate->subtotalScore,
            unitCost: $aggregate->unitCost,
            subtotalFee: $aggregate->subtotalFee,
            unmanagedCopay: $aggregate->unmanagedCopay,
            managedCopay: $aggregate->managedCopay,
            cappedCopay: $aggregate->cappedCopay,
            adjustedCopay: $aggregate->adjustedCopay,
            coordinatedCopay: $aggregate->coordinatedCopay,
            subtotalCopay: $aggregate->subtotalCopay,
            subtotalBenefit: $aggregate->subtotalBenefit,
            subtotalSubsidy: $aggregate->subtotalSubsidy,
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            $this->serviceDivisionCode->value(),
            1,
            $this->serviceDays,
            $this->subtotalScore,
            $this->unitCost->toInt(self::UNIT_CONST_FRACTION_DIGITS),
            0,
            $this->subtotalFee,
            $this->unmanagedCopay,
            $this->managedCopay,
            $this->cappedCopay,
            self::UNUSED,
            self::UNUSED,
            $this->adjustedCopay ?? '',
            $this->coordinatedCopay ?? '',
            $this->subtotalCopay,
            $this->subtotalBenefit,
            self::UNUSED,
            self::UNUSED,
            $this->subtotalSubsidy ?? '',
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
            self::UNUSED,
        ];
    }
}
