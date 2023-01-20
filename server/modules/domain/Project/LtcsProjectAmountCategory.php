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
 * 介護保険サービス：計画：サービス提供量区分.
 *
 * @method static LtcsProjectAmountCategory physicalCare() 身体介護
 * @method static LtcsProjectAmountCategory housework() 生活援助
 * @method static LtcsProjectAmountCategory ownExpense() 自費
 */
final class LtcsProjectAmountCategory extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 11,
        'housework' => 12,
        'ownExpense' => 91,
    ];
}
