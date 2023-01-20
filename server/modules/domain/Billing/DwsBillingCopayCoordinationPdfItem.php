<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 利用者負担上限額管理結果票 PDF 明細.
 */
final class DwsBillingCopayCoordinationPdfItem extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingCopayCoordinationPdfItem} constructor.
     *
     * @param int $itemNumber 項番
     * @param string $officeCode 事業所番号
     * @param string $officeName 事業所名
     * @param int $fee 総費用額
     * @param int $copay 利用者負担額
     * @param int $coordinatedCopay 管理結果後利用者負担額
     */
    public function __construct(
        public readonly int $itemNumber,
        public readonly string $officeCode,
        public readonly string $officeName,
        public readonly int $fee,
        public readonly int $copay,
        public readonly int $coordinatedCopay,
    ) {
    }
}
