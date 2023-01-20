<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Enum;

/**
 * 障害福祉サービス：計画：サービス区分.
 *
 * @method static DwsProjectServiceCategory physicalCare() 居宅：身体介護
 * @method static DwsProjectServiceCategory housework() 居宅：家事援助
 * @method static DwsProjectServiceCategory accompanyWithPhysicalCare() 居宅：通院等介助（身体を伴う）
 * @method static DwsProjectServiceCategory accompany() 居宅：通院等介助（身体を伴わない）
 * @method static DwsProjectServiceCategory visitingCareForPwsd() 重度訪問介護
 * @method static DwsProjectServiceCategory ownExpense() 自費
 */
final class DwsProjectServiceCategory extends Enum
{
    use DwsProjectServiceCategorySupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 11,
        'housework' => 12,
        'accompanyWithPhysicalCare' => 13,
        'accompany' => 14,
        'visitingCareForPwsd' => 21,
        'ownExpense' => 91,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        11 => '居宅：身体介護',
        12 => '居宅：家事援助',
        13 => '居宅：通院等介助（身体を伴う）',
        14 => '居宅：通院等介助（身体を伴わない）',
        21 => '重度訪問介護',
        91 => '自費',
    ];

    /**
     * Resolve DwsProjectServiceCategory to label.
     *
     * @param \Domain\Project\DwsProjectServiceCategory $x
     * @return string
     */
    public static function resolve(DwsProjectServiceCategory $x): string
    {
        return self::$map[$x->value()];
    }
}
