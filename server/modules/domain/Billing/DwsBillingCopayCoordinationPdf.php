<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Pdf\PdfSupport;
use Domain\Polite;
use ScalikePHP\Seq;

/**
 * 利用者負担上限額管理結果票 PDF.
 */
final class DwsBillingCopayCoordinationPdf extends Polite
{
    use PdfSupport;

    /**
     * {@link \Domain\Billing\DwsBillingCopayCoordinationPdf} constructor.
     *
     * @param array $providedIn サービス提供年月
     * @param \Domain\Billing\DwsBillingOffice $office 上限管理事業所
     * @param string $cityCode 市町村番号
     * @param \Domain\Billing\DwsBillingUser $user 上限管理対象利用者
     * @param \Domain\Billing\CopayCoordinationResult $result 利用者負担上限額管理結果
     * @param \Domain\Billing\DwsBillingCopayCoordinationPdfItem[]&\ScalikePHP\Seq $items 利用者負担額集計・調整欄
     * @param \Domain\Billing\DwsBillingCopayCoordinationPayment $total 合計
     */
    public function __construct(
        public readonly array $providedIn,
        public readonly DwsBillingOffice $office,
        public readonly string $cityCode,
        public readonly DwsBillingUser $user,
        public readonly CopayCoordinationResult $result,
        public readonly Seq $items,
        public readonly DwsBillingCopayCoordinationPayment $total
    ) {
    }

    /**
     * 利用者負担上限額管理結果票 PDF ドメインモデルを生成する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingCopayCoordination $copayCoordination
     * @return static
     */
    public static function from(DwsBillingBundle $bundle, DwsBillingCopayCoordination $copayCoordination): self
    {
        return new self(
            providedIn: self::localized($bundle->providedIn),
            office: $copayCoordination->office,
            cityCode: $bundle->cityCode,
            user: $copayCoordination->user,
            result: $copayCoordination->result,
            items: Seq::fromArray($copayCoordination->items)
                ->map(
                    fn (DwsBillingCopayCoordinationItem $x): DwsBillingCopayCoordinationPdfItem => new DwsBillingCopayCoordinationPdfItem(
                        itemNumber: $x->itemNumber,
                        officeCode: $x->office->code,
                        officeName: $x->office->name,
                        fee: $x->subtotal->fee,
                        copay: $x->subtotal->copay,
                        coordinatedCopay: $x->subtotal->coordinatedCopay
                    )
                ),
            total: $copayCoordination->total
        );
    }
}
