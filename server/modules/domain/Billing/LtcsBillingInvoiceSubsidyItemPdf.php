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
 * 介護保険サービス：請求書 PDF：公費請求：明細
 *
 * @property-read string $statementCount サービス費用：件数
 * @property-read string $totalScore サービス費用：単位数
 * @property-read string $totalFee サービス費用：費用合計
 * @property-read string $subsidyAmount サービス費用：公費請求額
 */
final class LtcsBillingInvoiceSubsidyItemPdf extends Model
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
            ->map(fn (LtcsBillingInvoice $x): LtcsBillingInvoiceSubsidyItemPdf => self::create([
                'statementCount' => number_format($x->statementCount),
                'totalScore' => number_format($x->totalScore),
                'totalFee' => number_format($x->totalFee),
                'subsidyAmount' => number_format($x->subsidyAmount),
            ]))
            ->getOrElseValue(self::create([
                'statementCount' => '',
                'totalScore' => '',
                'totalFee' => '',
                'subsidyAmount' => '',
            ]));
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'statementCount',
            'totalScore',
            'totalFee',
            'subsidyAmount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'statementCount' => true,
            'totalScore' => true,
            'totalFee' => true,
            'subsidyAmount' => true,
        ];
    }
}
