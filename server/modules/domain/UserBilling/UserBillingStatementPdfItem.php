<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Polite;
use ScalikePHP\Map;

/**
 * 利用者請求：介護サービス利用明細書 PDF 明細
 */
final class UserBillingStatementPdfItem extends Polite
{
    /**
     * {@link \Domain\UserBilling\UserBillingStatementPdfItem} constructor
     *
     * @param string $serviceCode サービスコード
     * @param string $serviceName サービス内容
     * @param string $unitScore 単価
     * @param string $count 数量
     * @param string $totalScore 小計
     */
    public function __construct(
        public readonly string $serviceCode,
        public readonly string $serviceName,
        public readonly string $unitScore,
        public readonly string $count,
        public readonly string $totalScore
    ) {
    }

    /**
     * 利用者請求：介護サービス利用明細書 PDF 明細 ドメインモデル（障害）を生成する.
     *
     * @param \Domain\Billing\DwsBillingStatementItem $item
     * @param \ScalikePHP\Map $serviceCodeMap サービス名称Map [サービスコード => 名称, ...]
     * @return static
     */
    public static function fromDws(DwsBillingStatementItem $item, Map $serviceCodeMap): self
    {
        return new self(
            serviceCode: $item->serviceCode->toString(),
            serviceName: $serviceCodeMap->getOrElse(
                $item->serviceCode->toString(),
                fn (): string => ''
            ),
            unitScore: number_format($item->unitScore),
            count: number_format($item->count),
            totalScore: number_format($item->totalScore),
        );
    }

    /**
     * 利用者請求：介護サービス利用明細書 PDF 明細 ドメインモデル（介保）を生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementItem $item
     * @param \ScalikePHP\Map $serviceCodeMap サービス名称Map [サービスコード => 名称, ...]
     * @return static
     */
    public static function fromLtcs(LtcsBillingStatementItem $item, Map $serviceCodeMap): self
    {
        return new self(
            serviceCode: $item->serviceCode->toString(),
            serviceName: $serviceCodeMap->getOrElse(
                $item->serviceCode->toString(),
                fn (): string => ''
            ),
            unitScore: number_format($item->unitScore),
            count: number_format($item->count),
            totalScore: number_format($item->totalScore),
        );
    }
}
