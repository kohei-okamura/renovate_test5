<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\UserBillingUsedService;

use Domain\Enum;

/**
 * 利用者請求：利用サービス.
 *
 * @method static UserBillingUsedService disabilitiesWelfareService() 障害福祉サービス
 * @method static UserBillingUsedService longTermCareService() 介護保険サービス
 * @method static UserBillingUsedService ownExpenseService() 自費サービス
 */
final class UserBillingUsedService extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'disabilitiesWelfareService' => 1,
        'longTermCareService' => 2,
        'ownExpenseService' => 3,
    ];
}
