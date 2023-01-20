<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\Model;

/**
 * 障害福祉サービス：明細書：契約.
 *
 * @property-read \Domain\Billing\DwsGrantedServiceCode $dwsGrantedServiceCode 決定サービスコード
 * @property-read int $grantedAmount 契約支給量（分単位）
 * @property-read \Domain\Common\Carbon $agreedOn 契約開始年月日
 * @property-read null|\Domain\Common\Carbon $expiredOn 契約終了年月日
 * @property-read int $indexNumber 事業者記入欄番号
 */
final class DwsBillingStatementContract extends Model
{
    /**
     * 障害福祉サービス受給者証：訪問系サービス事業者記入欄からインスタンスを生成する.
     *
     * @param \Domain\DwsCertification\DwsCertificationAgreement $agreement
     * @return static
     */
    public static function from(DwsCertificationAgreement $agreement): self
    {
        return self::create([
            'dwsGrantedServiceCode' => DwsGrantedServiceCode::fromDwsCertificationAgreementType(
                $agreement->dwsCertificationAgreementType
            ),
            'grantedAmount' => $agreement->paymentAmount,
            'agreedOn' => $agreement->agreedOn,
            'expiredOn' => $agreement->expiredOn,
            'indexNumber' => $agreement->indexNumber,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'dwsGrantedServiceCode',
            'grantedAmount',
            'agreedOn',
            'expiredOn',
            'indexNumber',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'dwsGrantedServiceCode' => true,
            'grantedAmount' => true,
            'agreedOn' => true,
            'expiredOn' => true,
            'indexNumber' => true,
        ];
    }
}
