<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;
use ScalikePHP\Map;

/**
 * 介護保険サービス：明細書 PDF 明細.
 */
final class LtcsBillingStatementPdfItem extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementPdfItem} constructor.
     *
     * @param string $serviceName サービス内容
     * @param string $serviceCode サービスコード
     * @param string $unitScore 単位数
     * @param string $count 回数
     * @param string $totalScore サービス単位数
     * @param string $subsidyCount 公費分回数
     * @param string $subsidyScore 公費対象単位数
     * @param string $note 摘要
     */
    public function __construct(
        public readonly string $serviceName,
        public readonly string $serviceCode,
        public readonly string $unitScore,
        public readonly string $count,
        public readonly string $totalScore,
        public readonly string $subsidyCount,
        public readonly string $subsidyScore,
        public readonly string $note
    ) {
    }

    /**
     * 明細書明細とサービスコードマップから介護保険サービス：明細書 PDF 明細を生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementItem $item
     * @param \ScalikePHP\Map $serviceCodeMap [[サービスコード => 辞書エントリ]]
     * @return \Domain\Billing\LtcsBillingStatementPdfItem
     */
    public static function from(
        LtcsBillingStatementItem $item,
        Map $serviceCodeMap,
    ): self {
        return new self(
            serviceName: $serviceCodeMap->getOrElse($item->serviceCode->toString(), fn (): string => ''),
            serviceCode: $item->serviceCode->toString(),
            unitScore: mb_strlen((string)$item->unitScore) > 4 ? mb_substr((string)$item->unitScore, 0, 4) : sprintf('% 4d', $item->unitScore),
            count: sprintf('% 2d', $item->count),
            totalScore: sprintf('% 6d', $item->totalScore),
            subsidyCount: isset($item->subsidies[0]) ? sprintf('% 2d', $item->subsidies[0]->count) : str_repeat(' ', 2),
            subsidyScore: isset($item->subsidies[0]) ? sprintf('% 6d', $item->subsidies[0]->totalScore) : str_repeat(' ', 6),
            note: $item->note,
        );
    }
}
