<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Enum;

/**
 * 利用者負担上限額管理結果票：作成区分.
 *
 * @method static DwsBillingCopayCoordinationExchangeAim declaration() 新規
 * @method static DwsBillingCopayCoordinationExchangeAim modification() 修正
 * @method static DwsBillingCopayCoordinationExchangeAim cancel() 取り消し
 */
final class DwsBillingCopayCoordinationExchangeAim extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'declaration' => 1,
        'modification' => 2,
        'cancel' => 3,
    ];
}
