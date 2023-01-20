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
 * 利用者請求：領収書 PDF
 *
 * @property-read \Domain\UserBilling\UserBillingReceiptPdfBillingDestination $billingDestination 請求先情報
 * @property-read int $carriedOverAmount 繰越金額
 * @property-read int $depositedAt 入金日時
 * @property-read null|\Domain\UserBilling\UserBillingDwsItem $dwsItem 障害福祉サービス明細
 * @property-read string $issuedOn 発行日
 * @property-read null|\Domain\UserBilling\UserBillingLtcsItem $ltcsItem 介護保険サービス明細
 * @property-read int $medicalDeductionAmount 医療費控除対象額
 * @property-read array $normalTaxRate 金額（10％）
 * @property-read \Domain\UserBilling\UserBillingOffice $office 事業所
 * @property-read int $otherItemsTotalAmount 自己負担サービスの合計金額
 * @property-read CarbonRange $period サービス提供年月（月初〜月末）
 * @property-read string $providedIn サービス提供年月（和暦）
 * @property-read array $reducedTaxRate 金額（8％）
 * @property-read int $totalAmount 合計金額
 * @property-read \Domain\UserBilling\UserBillingUser $user 利用者
 */
final class UserBillingReceiptPdf extends Model implements UserBillingPaymentPdf
{
    use PdfSupport;
    use UserBillingPdfSupport;

    /**
     * 利用者請求：領収書 PDF ドメインモデルを生成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\UserBilling\UserBilling $billing
     * @param \Domain\Common\Carbon $issuedOn
     * @return static
     */
    public static function from(User $user, UserBilling $billing, Carbon $issuedOn): self
    {
        $amounts = self::calculateAmounts($billing);
        $depositedAt = $billing->depositedAt === null ? '' : $billing->depositedAt->toJapaneseDate();
        return self::create([
            'billingDestination' => UserBillingReceiptPdfBillingDestination::from($user),
            'carriedOverAmount' => $billing->carriedOverAmount,
            'depositedAt' => $depositedAt,
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
            'depositedAt',
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
            'depositedAt' => true,
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
