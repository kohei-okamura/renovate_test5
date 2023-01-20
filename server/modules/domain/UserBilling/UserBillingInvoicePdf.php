<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Model;
use Domain\Pdf\PdfSupport;
use Domain\User\User;

/**
 * 利用者請求：請求書 PDF
 *
 * @property-read string $deductedOn 口座振替日
 * @property-read string $dueDate お支払期限日
 */
final class UserBillingInvoicePdf extends Model implements UserBillingPaymentPdf
{
    use PdfSupport;
    use UserBillingPdfSupport;

    /**
     * 利用者請求：請求書 PDF ドメインモデルを生成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\UserBilling\UserBilling $billing
     * @param \Domain\Common\Carbon $issuedOn
     * @return static
     */
    public static function from(User $user, UserBilling $billing, Carbon $issuedOn): self
    {
        $amounts = self::calculateAmounts($billing);
        return self::create([
            'billingDestination' => UserBillingReceiptPdfBillingDestination::from($user),
            'carriedOverAmount' => $billing->carriedOverAmount,
            'deductedOn' => $billing->deductedOn === null ? '' : $billing->deductedOn->toJapaneseDate(),
            'dueDate' => $billing->dueDate->toJapaneseDate(),
            'dwsItem' => $billing->dwsItem,
            'issuedOn' => $issuedOn->toJapaneseDate(),
            'ltcsItem' => $billing->ltcsItem,
            'medicalDeductionAmount' => $amounts['medicalDeductionAmount'],
            'normalTaxRate' => $amounts['normalTaxRate'],
            'office' => $billing->office,
            'otherItemsTotalAmount' => $amounts['otherItemsTotalAmount'],
            'period' => CarbonRange::ofMonth($billing->providedIn),
            'providedIn' => $billing->providedIn->toJapaneseYearMonth(),
            'reducedTaxRate' => $amounts['reducedTaxRate'],
            'totalAmount' => $billing->totalAmount,
            'user' => $billing->user,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'billingDestination',
            'carriedOverAmount',
            'deductedOn',
            'dueDate',
            'dwsItem',
            'issuedOn',
            'ltcsItem',
            'medicalDeductionAmount',
            'normalTaxRate',
            'office',
            'otherItemsTotalAmount',
            'period',
            'providedIn',
            'reducedTaxRate',
            'totalAmount',
            'user',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'billingDestination' => true,
            'carriedOverAmount' => true,
            'deductedOn' => true,
            'dueDate' => true,
            'dwsItem' => true,
            'issuedOn' => true,
            'ltcsItem' => true,
            'medicalDeductionAmount' => true,
            'normalTaxRate' => true,
            'office' => true,
            'otherItemsTotalAmount' => true,
            'period' => true,
            'providedIn' => true,
            'reducedTaxRate' => true,
            'totalAmount' => true,
            'user' => true,
        ];
    }
}
