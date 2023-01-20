<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Enum;

/**
 * 介護保険サービス：請求：サービス詳細区分.
 *
 * @method static LtcsBillingServiceDetailDisposition plan() 予定
 * @method static LtcsBillingServiceDetailDisposition result() 実績
 */
final class LtcsBillingServiceDetailDisposition extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'plan' => 1,
        'result' => 2,
    ];
}
