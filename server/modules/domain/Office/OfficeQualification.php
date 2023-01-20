<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 事業所：指定区分.
 *
 * @method static OfficeQualification dwsHomeHelpService() 居宅介護（障害福祉サービス）
 * @method static OfficeQualification dwsVisitingCareForPwsd() 重度訪問介護（障害福祉サービス）
 * @method static OfficeQualification dwsCommAccompany() 地域生活支援事業・移動支援（障害福祉サービス）
 * @method static OfficeQualification dwsOthers() その他障害福祉サービス
 * @method static OfficeQualification ltcsHomeVisitLongTermCare() 訪問介護（介護保険サービス）
 * @method static OfficeQualification ltcsCompHomeVisiting() 総合事業・訪問型サービス（介護保険サービス）
 * @method static OfficeQualification ltcsCareManagement() 居宅介護支援（介護保険サービス）
 * @method static OfficeQualification ltcsPrevention() 介護予防支援（介護保険サービス）
 * @method static OfficeQualification ltcsOthers() その他介護保険サービス
 */
final class OfficeQualification extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'dwsHomeHelpService' => '1011',
        'dwsVisitingCareForPwsd' => '1012',
        'dwsCommAccompany' => '1072',
        'dwsOthers' => '10ZZ',
        'ltcsHomeVisitLongTermCare' => '2011',
        'ltcsCompHomeVisiting' => '20A0',
        'ltcsCareManagement' => '2046',
        'ltcsPrevention' => '20A4',
        'ltcsOthers' => '20ZZ',
    ];
}
