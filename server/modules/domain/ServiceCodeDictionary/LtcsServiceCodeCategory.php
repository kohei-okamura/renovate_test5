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
 * 介護保険サービス：請求：サービスコード区分.
 *
 * @method static LtcsServiceCodeCategory physicalCare() 身体
 * @method static LtcsServiceCodeCategory physicalCareAndHousework() 身体＋生活
 * @method static LtcsServiceCodeCategory housework() 生活
 * @method static LtcsServiceCodeCategory emergencyAddition() 緊急時訪問介護加算
 * @method static LtcsServiceCodeCategory firstTimeAddition() 初回加算
 * @method static LtcsServiceCodeCategory vitalFunctionsImprovementAddition1() 生活機能向上連携加算Ⅰ
 * @method static LtcsServiceCodeCategory vitalFunctionsImprovementAddition2() 生活機能向上連携加算Ⅱ
 * @method static LtcsServiceCodeCategory bulkServiceSubtraction1() 同一建物減算Ⅰ
 * @method static LtcsServiceCodeCategory bulkServiceSubtraction2() 同一建物減算Ⅱ
 * @method static LtcsServiceCodeCategory treatmentImprovementAddition1() 介護職員処遇改善加算Ⅰ
 * @method static LtcsServiceCodeCategory treatmentImprovementAddition2() 介護職員処遇改善加算Ⅱ
 * @method static LtcsServiceCodeCategory treatmentImprovementAddition3() 介護職員処遇改善加算Ⅲ
 * @method static LtcsServiceCodeCategory treatmentImprovementAddition4() 介護職員処遇改善加算Ⅳ
 * @method static LtcsServiceCodeCategory treatmentImprovementAddition5() 介護職員処遇改善加算Ⅴ
 * @method static LtcsServiceCodeCategory specifiedTreatmentImprovementAddition1() 介護職員等特定処遇改善加算Ⅰ
 * @method static LtcsServiceCodeCategory specifiedTreatmentImprovementAddition2() 介護職員等特定処遇改善加算Ⅱ
 * @method static LtcsServiceCodeCategory symbioticServiceSubtraction1() 共生型サービス減算（居宅介護1）
 * @method static LtcsServiceCodeCategory symbioticServiceSubtraction2() 共生型サービス減算（居宅介護2）
 * @method static LtcsServiceCodeCategory symbioticServiceSubtraction3() 共生型サービス減算（重度訪問介護）
 * @method static LtcsServiceCodeCategory specifiedAreaAddition() 特別地域訪問介護加算
 * @method static LtcsServiceCodeCategory smallOfficeAddition() 小規模事業所加算（中山間地域等における小規模事業所加算）
 * @method static LtcsServiceCodeCategory mountainousAreaAddition() 中山間地域等提供加算（中山間地域等に居住する者へのサービス提供加算）
 * @method static LtcsServiceCodeCategory specifiedOfficeAddition1() 特定事業所加算Ⅰ
 * @method static LtcsServiceCodeCategory specifiedOfficeAddition2() 特定事業所加算Ⅱ
 * @method static LtcsServiceCodeCategory specifiedOfficeAddition3() 特定事業所加算Ⅲ
 * @method static LtcsServiceCodeCategory specifiedOfficeAddition4() 特定事業所加算Ⅳ
 * @method static LtcsServiceCodeCategory specifiedOfficeAddition5() 特定事業所加算Ⅴ
 * @method static LtcsServiceCodeCategory dementiaCareSpecialistAddition1() 認知症専門ケア加算Ⅰ
 * @method static LtcsServiceCodeCategory dementiaCareSpecialistAddition2() 認知症専門ケア加算Ⅱ
 * @method static LtcsServiceCodeCategory covid19PandemicSpecialAddition() 令和3年9月30日までの上乗せ分
 * @method static LtcsServiceCodeCategory baseIncreaseSupportAddition() 訪問介護ベースアップ等支援加算
 */
final class LtcsServiceCodeCategory extends Enum
{
    use LtcsServiceCodeCategorySupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 111000,
        'physicalCareAndHousework' => 112000,
        'housework' => 113000,
        'emergencyAddition' => 990101,
        'firstTimeAddition' => 990201,
        'vitalFunctionsImprovementAddition1' => 990301,
        'vitalFunctionsImprovementAddition2' => 990302,
        'bulkServiceSubtraction1' => 990401,
        'bulkServiceSubtraction2' => 990402,
        'treatmentImprovementAddition1' => 990501,
        'treatmentImprovementAddition2' => 990502,
        'treatmentImprovementAddition3' => 990503,
        'treatmentImprovementAddition4' => 990504,
        'treatmentImprovementAddition5' => 990505,
        'specifiedTreatmentImprovementAddition1' => 990601,
        'specifiedTreatmentImprovementAddition2' => 990602,
        'symbioticServiceSubtraction1' => 990701,
        'symbioticServiceSubtraction2' => 990702,
        'symbioticServiceSubtraction3' => 990711,
        'specifiedAreaAddition' => 990801,
        'smallOfficeAddition' => 990901,
        'mountainousAreaAddition' => 991001,
        'specifiedOfficeAddition1' => 991101,
        'specifiedOfficeAddition2' => 991102,
        'specifiedOfficeAddition3' => 991103,
        'specifiedOfficeAddition4' => 991104,
        'specifiedOfficeAddition5' => 991105,
        'dementiaCareSpecialistAddition1' => 991201,
        'dementiaCareSpecialistAddition2' => 991202,
        'covid19PandemicSpecialAddition' => 991301,
        'baseIncreaseSupportAddition' => 991401,
    ];
}
