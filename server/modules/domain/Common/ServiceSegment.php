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
 * 事業領域.
 *
 * @method static ServiceSegment disabilitiesWelfare() 障害福祉サービス
 * @method static ServiceSegment longTermCare() 介護保険サービス
 * @method static ServiceSegment comprehensive() 総合事業
 * @method static ServiceSegment communityLifeSupport() 地域生活支援事業
 * @method static ServiceSegment ownExpense() 自費サービス
 * @method static ServiceSegment other() その他
 */
final class ServiceSegment extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'disabilitiesWelfare' => 1,
        'longTermCare' => 2,
        'comprehensive' => 3,
        'communityLifeSupport' => 4,
        'ownExpense' => 7,
        'other' => 9,
    ];
}
