<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Enum;

/**
 * 障害福祉サービス受給者証：サービス内容.
 *
 * @method static DwsCertificationAgreementType physicalCare() 身体介護
 * @method static DwsCertificationAgreementType housework() 家事援助
 * @method static DwsCertificationAgreementType accompanyWithPhysicalCare() 通院介助（身体介護を伴う）
 * @method static DwsCertificationAgreementType accompany() 通院介助（身体介護を伴わない）
 * @method static DwsCertificationAgreementType visitingCareForPwsd1() 重度訪問介護（重度障害者等包括支援対象者）
 * @method static DwsCertificationAgreementType visitingCareForPwsd2() 重度訪問介護（障害支援区分6該当者）
 * @method static DwsCertificationAgreementType visitingCareForPwsd3() 重度訪問介護（その他）
 * @method static DwsCertificationAgreementType outingSupportForPwsd() 重度訪問介護（移動加算）
 */
final class DwsCertificationAgreementType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 11,
        'housework' => 12,
        'accompanyWithPhysicalCare' => 13,
        'accompany' => 14,
        'visitingCareForPwsd1' => 21,
        'visitingCareForPwsd2' => 22,
        'visitingCareForPwsd3' => 23,
        'outingSupportForPwsd' => 29,
    ];
}
