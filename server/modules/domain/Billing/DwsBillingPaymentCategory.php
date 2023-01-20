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
 * 障害福祉サービス：請求：給付種別.
 *
 * @method static DwsBillingPaymentCategory category1() 介護給付費・訓練等給付費・地域相談支援給付費・特例介護給付費・特例訓練等給付費
 * @method static DwsBillingPaymentCategory category2() 特定障害者特別給付費・高額障害者福祉サービス費
 */
final class DwsBillingPaymentCategory extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'category1' => 1,
        'category2' => 2,
    ];
}
