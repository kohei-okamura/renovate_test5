<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;
use ScalikePHP\Option;

/**
 * 介護保険サービス：請求書 PDF：保険請求
 *
 * @property-read string $statementCount サービス費用：件数
 * @property-read string $totalScore サービス費用：単位数
 * @property-read string $totalFee サービス費用：費用合計
 * @property-read string $insuranceAmount サービス費用：保険請求額
 * @property-read string $subsidyAmount サービス費用：公費請求額
 * @property-read string $copayAmount サービス費用：利用者負担
 */
final class LtcsBillingInvoiceInsurancePdf extends Model
{
    /**
     * 介護保険サービス：請求書からインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Option $invoice
     * @return static
     */
    public static function from(Option $invoice): self
    {
        return $invoice
            ->map(fn (LtcsBillingInvoice $x): LtcsBillingInvoiceInsurancePdf => self::create([
                'statementCount' => number_format($x->statementCount),
                'totalScore' => number_format($x->totalScore),
                'totalFee' => number_format($x->totalFee),
                'insuranceAmount' => number_format($x->insuranceAmount),
                'subsidyAmount' => number_format($x->subsidyAmount),
                'copayAmount' => number_format($x->copayAmount),
            ]))
            ->getOrElseValue(self::create([
                'statementCount' => '',
                'totalScore' => '',
                'totalFee' => '',
                'insuranceAmount' => '',
                'subsidyAmount' => '',
                'copayAmount' => '',
            ]));
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'statementCount',
            'totalScore',
            'totalFee',
            'insuranceAmount',
            'subsidyAmount',
            'copayAmount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'statementCount' => true,
            'totalScore' => true,
            'totalFee' => true,
            'insuranceAmount' => true,
            'subsidyAmount' => true,
            'copayAmount' => true,
        ];
    }
}
