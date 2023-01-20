<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Enum;

/**
 * 介護保険サービス：計画：サービス区分.
 *
 * @method static LtcsProjectServiceCategory physicalCare() 身体介護
 * @method static LtcsProjectServiceCategory housework() 生活援助
 * @method static LtcsProjectServiceCategory physicalCareAndHousework() 身体・生活
 * @method static LtcsProjectServiceCategory ownExpense() 自費
 */
final class LtcsProjectServiceCategory extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 11,
        'housework' => 12,
        'physicalCareAndHousework' => 13,
        'ownExpense' => 91,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        11 => '身体介護',
        12 => '生活援助',
        13 => '身体・生活',
        91 => '自費',
    ];

    /**
     * Resolve LtcsProjectServiceCategory to label.
     *
     * @param \Domain\Project\LtcsProjectServiceCategory $x
     * @return string
     */
    public static function resolve(LtcsProjectServiceCategory $x): string
    {
        return self::$map[$x->value()];
    }
}
