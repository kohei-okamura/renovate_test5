<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Model;

/**
 * 障害福祉サービス受給者証：訪問系サービス事業者記入欄.
 *
 * @property-read int $indexNumber 番号
 * @property-read int $officeId 事業所ID
 * @property-read \Domain\DwsCertification\DwsCertificationAgreementType $dwsCertificationAgreementType サービス内容
 * @property-read int $paymentAmount 契約支給量（分単位）
 * @property-read \Domain\Common\Carbon $agreedOn 契約日
 * @property-read null|\Domain\Common\Carbon $expiredOn 当該契約支給量によるサービス提供終了日
 */
final class DwsCertificationAgreement extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'indexNumber',
            'officeId',
            'dwsCertificationAgreementType',
            'paymentAmount',
            'agreedOn',
            'expiredOn',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'indexNumber' => true,
            'officeId' => true,
            'dwsCertificationAgreementType' => true,
            'paymentAmount' => true,
            'agreedOn' => true,
            'expiredOn' => true,
        ];
    }
}
