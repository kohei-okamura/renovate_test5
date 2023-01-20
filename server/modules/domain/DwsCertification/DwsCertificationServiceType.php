<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Enum;

/**
 * 障害福祉サービス受給者証：サービス種別.
 *
 * @method static DwsCertificationServiceType physicalCare() 居宅介護：居宅における身体介護中心
 * @method static DwsCertificationServiceType housework() 居宅介護：家事援助中心
 * @method static DwsCertificationServiceType accompanyWithPhysicalCare() 居宅介護：通院等介助（身体介護を伴う場合）中心
 * @method static DwsCertificationServiceType accompany() 居宅介護：通院等介助（身体介護を伴わない場合）中心
 * @method static DwsCertificationServiceType visitingCareForPwsd1() 重度訪問介護（重度障害者等包括支援対象者）
 * @method static DwsCertificationServiceType visitingCareForPwsd2() 重度訪問介護（障害支援区分6該当者）
 * @method static DwsCertificationServiceType visitingCareForPwsd3() 重度訪問介護（その他）
 */
final class DwsCertificationServiceType extends Enum
{
    use DwsCertificationServiceTypeSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 1,
        'housework' => 2,
        'accompanyWithPhysicalCare' => 3,
        'accompany' => 4,
        'visitingCareForPwsd1' => 7,
        'visitingCareForPwsd2' => 8,
        'visitingCareForPwsd3' => 9,
    ];
}
