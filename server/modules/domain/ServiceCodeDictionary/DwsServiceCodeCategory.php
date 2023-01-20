<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Enum;

/**
 * 障害福祉サービス：請求：サービスコード区分.
 *
 * @method static DwsServiceCodeCategory physicalCare() 居宅：身体
 * @method static DwsServiceCodeCategory housework() 居宅：家事
 * @method static DwsServiceCodeCategory accompanyWithPhysicalCare() 居宅：通院・身体あり
 * @method static DwsServiceCodeCategory accompany() 居宅：通院・身体なし
 * @method static DwsServiceCodeCategory accessibleTaxi() 居宅：乗降介助
 * @method static DwsServiceCodeCategory visitingCareForPwsd1() 重訪Ⅰ（重度障害者等の場合）
 * @method static DwsServiceCodeCategory visitingCareForPwsd2() 重訪Ⅱ（障害支援区分6に該当する者の場合）
 * @method static DwsServiceCodeCategory visitingCareForPwsd3() 重訪Ⅲ
 * @method static DwsServiceCodeCategory outingSupportForPwsd() 重訪（移動加算）
 * @method static DwsServiceCodeCategory specifiedOfficeAddition1() 特定事業所加算Ⅰ
 * @method static DwsServiceCodeCategory specifiedOfficeAddition2() 特定事業所加算Ⅱ
 * @method static DwsServiceCodeCategory specifiedOfficeAddition3() 特定事業所加算Ⅲ
 * @method static DwsServiceCodeCategory specifiedOfficeAddition4() 特定事業所加算Ⅳ
 * @method static DwsServiceCodeCategory specifiedAreaAddition() 特別地域加算
 * @method static DwsServiceCodeCategory emergencyAddition1() 緊急時対応加算
 * @method static DwsServiceCodeCategory emergencyAddition2() 緊急時対応加算（地域生活拠点）
 * @method static DwsServiceCodeCategory suckingSupportSystemAddition() 喀痰吸引等支援体制加算
 * @method static DwsServiceCodeCategory firstTimeAddition() 初回加算
 * @method static DwsServiceCodeCategory copayCoordinationAddition() 利用者負担上限額管理加算
 * @method static DwsServiceCodeCategory welfareSpecialistCooperationAddition() 福祉専門職員等連携加算
 * @method static DwsServiceCodeCategory behavioralDisorderSupportCooperationAddition() 行動障害支援連携加算
 * @method static DwsServiceCodeCategory treatmentImprovementAddition1() 福祉・介護職員処遇改善加算Ⅰ
 * @method static DwsServiceCodeCategory treatmentImprovementAddition2() 福祉・介護職員処遇改善加算Ⅱ
 * @method static DwsServiceCodeCategory treatmentImprovementAddition3() 福祉・介護職員処遇改善加算Ⅲ
 * @method static DwsServiceCodeCategory treatmentImprovementAddition4() 福祉・介護職員処遇改善加算Ⅳ
 * @method static DwsServiceCodeCategory treatmentImprovementAddition5() 福祉・介護職員処遇改善加算Ⅴ
 * @method static DwsServiceCodeCategory treatmentImprovementSpecialAddition() 福祉・介護職員処遇改善特別加算
 * @method static DwsServiceCodeCategory specifiedTreatmentImprovementAddition1() 福祉・介護職員等特定処遇改善加算Ⅰ
 * @method static DwsServiceCodeCategory specifiedTreatmentImprovementAddition2() 福祉・介護職員等特定処遇改善加算Ⅱ
 * @method static DwsServiceCodeCategory covid19PandemicSpecialAddition() 令和3年9月30日までの上乗せ分
 * @method static DwsServiceCodeCategory bulkServiceSubtraction1() 同一建物減算1
 * @method static DwsServiceCodeCategory bulkServiceSubtraction2() 同一建物減算2
 * @method static DwsServiceCodeCategory physicalRestraintSubtraction() 身体拘束廃止未実施減算
 * @method static DwsServiceCodeCategory movingCareSupportAddition() 移動介護緊急時支援加算
 * @method static DwsServiceCodeCategory baseIncreaseSupportAddition() 福祉・介護職員等ベースアップ等支援加算
 */
final class DwsServiceCodeCategory extends Enum
{
    use DwsServiceCodeCategorySupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 111000,
        'housework' => 112000,
        'accompanyWithPhysicalCare' => 113000,
        'accompany' => 114000,
        'accessibleTaxi' => 115000,
        'visitingCareForPwsd1' => 121000,
        'visitingCareForPwsd2' => 122000,
        'visitingCareForPwsd3' => 123000,
        'outingSupportForPwsd' => 120901,
        'specifiedOfficeAddition1' => 990101,
        'specifiedOfficeAddition2' => 990102,
        'specifiedOfficeAddition3' => 990103,
        'specifiedOfficeAddition4' => 990104,
        'specifiedAreaAddition' => 990201,
        'emergencyAddition1' => 990301,
        'emergencyAddition2' => 990302,
        'suckingSupportSystemAddition' => 990401,
        'firstTimeAddition' => 990501,
        'copayCoordinationAddition' => 990601,
        'welfareSpecialistCooperationAddition' => 990701,
        'behavioralDisorderSupportCooperationAddition' => 990702,
        'treatmentImprovementAddition1' => 990801,
        'treatmentImprovementAddition2' => 990802,
        'treatmentImprovementAddition3' => 990803,
        'treatmentImprovementAddition4' => 990804,
        'treatmentImprovementAddition5' => 990805,
        'treatmentImprovementSpecialAddition' => 990901,
        'specifiedTreatmentImprovementAddition1' => 991001,
        'specifiedTreatmentImprovementAddition2' => 991002,
        'covid19PandemicSpecialAddition' => 991101,
        'bulkServiceSubtraction1' => 991201,
        'bulkServiceSubtraction2' => 991202,
        'physicalRestraintSubtraction' => 991301,
        'movingCareSupportAddition' => 991401,
        'baseIncreaseSupportAddition' => 991501,
    ];
}
