<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * 公費制度（法別番号）.
 *
 * @method static DefrayerCategory pwdSupport() 【58】特別対策（全額免除）
 * @method static DefrayerCategory atomicBombVictim() 【81】原爆（福祉）
 * @method static DefrayerCategory supportForJapaneseReturneesFromChina() 【25】中国残留邦人
 * @method static DefrayerCategory livelihoodProtection() 【12】生活保護
 */
final class DefrayerCategory extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'pwdSupport' => 58,
        'atomicBombVictim' => 81,
        'supportForJapaneseReturneesFromChina' => 25,
        'livelihoodProtection' => 12,
    ];
}
