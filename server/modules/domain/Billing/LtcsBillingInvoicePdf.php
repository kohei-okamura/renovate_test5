<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;
use Domain\Pdf\PdfSupport;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求書 PDF
 *
 * @property-read \Domain\Billing\LtcsBillingOffice $office 事業所
 * @property-read array $providedIn サービス提供年月
 * @property-read \Domain\Billing\LtcsBillingInvoiceInsurancePdf $insurance 保険請求
 * @property-read \Domain\Billing\LtcsBillingInvoiceSubsidyPdf $subsidy 公費請求
 */
final class LtcsBillingInvoicePdf extends Model
{
    use PdfSupport;

    /**
     * 介護保険サービス：請求、介護保険サービス：請求単位、介護保険サービス：請求書からインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq $invoices
     * @return static
     */
    public static function from(LtcsBilling $billing, LtcsBillingBundle $bundle, Seq $invoices): self
    {
        return self::create([
            'office' => $billing->office,
            'providedIn' => self::localized($bundle->providedIn),
            'insurance' => LtcsBillingInvoiceInsurancePdf::from(
                $invoices->filter(fn (LtcsBillingInvoice $x): bool => !$x->isSubsidy)->headOption()
            ),
            'subsidy' => LtcsBillingInvoiceSubsidyPdf::from(
                $invoices->filter(fn (LtcsBillingInvoice $x): bool => $x->isSubsidy)
            ),
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'office',
            'providedIn',
            'insurance',
            'subsidy',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'office' => true,
            'providedIn' => true,
            'insurance' => true,
            'subsidy' => true,
        ];
    }
}
