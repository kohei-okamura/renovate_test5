<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;
use ScalikePHP\Map;

/**
 * 障害福祉サービス：明細書 PDF 明細.
 */
class DwsBillingStatementPdfItem extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingStatementPdfItem} constructor.
     *
     * @param string $serviceName
     * @param string $serviceCode
     * @param string $unitScore
     * @param string $count
     * @param string $totalScore
     */
    public function __construct(
        public readonly string $serviceName,
        public readonly string $serviceCode,
        public readonly string $unitScore,
        public readonly string $count,
        public readonly string $totalScore,
    ) {
    }

    /**
     * 障害福祉サービス：明細書 明細 を PDF に描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingStatementItem $item
     * @param \ScalikePHP\Map $serviceCodeMap [[サービスコード => 辞書エントリ]]
     * @return self
     */
    public static function from(DwsBillingStatementItem $item, Map $serviceCodeMap): self
    {
        return new self(
            serviceName: $serviceCodeMap->getOrElse($item->serviceCode->toString(), fn (): string => ''),
            serviceCode: $item->serviceCode->toString(),
            unitScore: mb_strlen((string)$item->unitScore) > 4 ? mb_substr((string)$item->unitScore, 0, 4) : self::convertToFixedLengthString($item->unitScore, 4),
            count: self::convertToFixedLengthString($item->count, 3),
            totalScore: self::convertToFixedLengthString($item->totalScore, 5),
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
