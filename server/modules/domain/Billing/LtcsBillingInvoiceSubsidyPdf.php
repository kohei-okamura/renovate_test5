<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\DefrayerCategory;
use Domain\Model;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求書 PDF：公費請求
 *
 * @property-read array $items 明細 {'12': 生活保護：明細, '81': 原爆（福祉）：明細, '58': 特別対策（全額免除）：明細, '25': 中国残留邦人：明細}
 * @property-read string $subsidyAmountTotal 公費請求額合計
 */
final class LtcsBillingInvoiceSubsidyPdf extends Model
{
    /**
     * 介護保険サービス：請求書からインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq $invoices
     * @return static
     */
    public static function from(Seq $invoices): self
    {
        return self::create([
            'items' => [
                DefrayerCategory::livelihoodProtection()->value() => LtcsBillingInvoiceSubsidyItemPdf::from(
                    $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::livelihoodProtection())
                ),
                DefrayerCategory::atomicBombVictim()->value() => LtcsBillingInvoiceSubsidyItemPdf::from(
                    $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::atomicBombVictim())
                ),
                DefrayerCategory::pwdSupport()->value() => LtcsBillingInvoiceSubsidyItemPdf::from(
                    $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::pwdSupport())
                ),
                DefrayerCategory::supportForJapaneseReturneesFromChina()->value() => LtcsBillingInvoiceSubsidyItemPdf::from(
                    $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::supportForJapaneseReturneesFromChina())
                ),
            ],
            'subsidyAmountTotal' => number_format(
                $invoices->map(fn (LtcsBillingInvoice $x): int => $x->subsidyAmount)->sum()
            ),
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'items',
            'subsidyAmountTotal',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'items' => true,
            'subsidyAmountTotal' => true,
        ];
    }
}
