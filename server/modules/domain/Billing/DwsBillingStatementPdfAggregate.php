<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 障害福祉サービス：明細書 PDF 集計.
 */
class DwsBillingStatementPdfAggregate extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingStatementPdfAggregate} constructor.
     *
     * @param string $serviceDivisionCode サービス種類コード
     * @param string $resolvedServiceDivisionCode サービス種類名称
     * @param string $serviceDays サービス利用日数
     * @param string $subtotalScore 給付単位数
     * @param string $unitCost 単位数単価
     * @param string $subtotalFee 総費用額
     * @param string $unmanagedCopay 1割相当額
     * @param string $managedCopay 利用者負担額
     * @param string $cappedCopay 上限月額調整
     * @param string $adjustedCopay 調整後利用者負担額
     * @param string $coordinatedCopay 上限額管理後利用者負担額
     * @param string $subtotalCopay 決定利用者負担額
     * @param string $subtotalBenefit 請求額：給付費
     * @param string $subtotalSubsidy 自治体助成分請求額
     */
    public function __construct(
        public readonly string $serviceDivisionCode,
        public readonly string $resolvedServiceDivisionCode,
        public readonly string $serviceDays,
        public readonly string $subtotalScore,
        public readonly string $unitCost,
        public readonly string $subtotalFee,
        public readonly string $unmanagedCopay,
        public readonly string $managedCopay,
        public readonly string $cappedCopay,
        public readonly string $adjustedCopay,
        public readonly string $coordinatedCopay,
        public readonly string $subtotalCopay,
        public readonly string $subtotalBenefit,
        public readonly string $subtotalSubsidy,
    ) {
    }

    /**
     * 障害福祉サービス：明細書 明細 を PDF に描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingStatementAggregate $aggregate
     * @return self
     */
    public static function from(DwsBillingStatementAggregate $aggregate): self
    {
        return new self(
            serviceDivisionCode: self::convertToFixedLengthString($aggregate->serviceDivisionCode->value(), 2),
            resolvedServiceDivisionCode: DwsServiceDivisionCode::resolve($aggregate->serviceDivisionCode),
            serviceDays: self::convertToFixedLengthString($aggregate->serviceDays, 2),
            subtotalScore: self::convertToFixedLengthString($aggregate->subtotalScore),
            unitCost: self::convertToFixedLengthString($aggregate->unitCost->toInt(2), 4),
            subtotalFee: self::convertToFixedLengthString($aggregate->subtotalFee),
            unmanagedCopay: self::convertToFixedLengthString($aggregate->unmanagedCopay),
            managedCopay: self::convertToFixedLengthString($aggregate->managedCopay),
            cappedCopay: self::convertToFixedLengthString($aggregate->cappedCopay),
            adjustedCopay: self::convertToFixedLengthString($aggregate->adjustedCopay),
            coordinatedCopay: self::convertToFixedLengthString($aggregate->coordinatedCopay),
            subtotalCopay: self::convertToFixedLengthString($aggregate->subtotalCopay),
            subtotalBenefit: self::convertToFixedLengthString($aggregate->subtotalBenefit),
            subtotalSubsidy: self::convertToFixedLengthString($aggregate->subtotalSubsidy),
        );
    }

    /**
     * 数値を固定帳の文字列にして返す（足りない分は前に空白を追加する）
     * 数値が null の場合は指定桁数の空文字列を返す
     *
     * @param null|int|string $value
     * @param int $digits 桁数（7桁が多いのでデフォルトは 7）
     * @return string
     */
    private static function convertToFixedLengthString(int|string|null $value, int $digits = 7): string
    {
        return $value !== null ? sprintf("% {$digits}d", $value) : str_repeat(' ', $digits);
    }
}
