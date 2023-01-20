<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;
use ScalikePHP\Seq;

/**
 * 利用者負担額一覧表 PDF 明細.
 */
final class CopayListPdfItem extends Polite
{
    /**
     * {@link \Domain\Billing\CopayListPdfItem} constructor.
     *
     * @param int $itemNumber 項番
     * @param string $cityCode 市町村番号
     * @param string $dwsNumber 受給者証番号
     * @param string $name 氏名
     * @param int $fee 総費用額
     * @param int $copay 利用者負担額
     * @param array $serviceDivision 提供サービス
     */
    public function __construct(
        public readonly int $itemNumber,
        public readonly string $cityCode,
        public readonly string $dwsNumber,
        public readonly string $name,
        public readonly int $fee,
        public readonly int $copay,
        public readonly array $serviceDivision,
    ) {
    }

    /**
     * 利用者負担額一覧表 PDF 明細ドメインモデルを生成する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param int $index
     * @return self
     */
    public static function from(DwsBillingBundle $bundle, DwsBillingStatement $statement, int $index): self
    {
        $copay = $statement->totalAdjustedCopay === null
            ? $statement->totalCappedCopay
            : min($statement->totalCappedCopay, $statement->totalAdjustedCopay);
        return new self(
            itemNumber: $index,
            cityCode: $bundle->cityCode,
            dwsNumber: $statement->user->dwsNumber,
            name: $statement->user->name->displayName,
            fee: $statement->totalFee,
            copay: $copay,
            serviceDivision: Seq::fromArray($statement->aggregates)
                ->map(fn (DwsBillingStatementAggregate $x): DwsServiceDivisionCode => $x->serviceDivisionCode)
                ->toArray()
        );
    }
}
