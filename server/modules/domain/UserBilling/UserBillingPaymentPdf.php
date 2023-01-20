<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

/**
 * 利用者請求関連 PDF 請求書/領収書共通インターフェース.
 *
 * @property-read array $billingDestination 請求先情報
 * @property-read int $carriedOverAmount 繰越金額
 * @property-read \Domain\UserBilling\UserBillingDwsItem $dwsItem 障害福祉サービス明細
 * @property-read string $issuedOn 発行日
 * @property-read \Domain\UserBilling\UserBillingLtcsItem $ltcsItem 介護保険サービス明細
 * @property-read int $medicalDeductionAmount 特定障害者特別給付費
 * @property-read array $normalTaxRate 合計：通常税率
 * @property-read \Domain\UserBilling\UserBillingOffice $office 事業所
 * @property-read int $otherItemsTotalAmount 合計：その他サービス
 * @property-read \Domain\Common\CarbonRange $period サービス提供期間
 * @property-read string $providedIn サービス提供年月
 * @property-read array $reducedTaxRate 合計：軽減税率
 * @property-read int $totalAmount 合計
 * @property-read \Domain\UserBilling\UserBillingUser $user 利用者
 */
interface UserBillingPaymentPdf
{
}
