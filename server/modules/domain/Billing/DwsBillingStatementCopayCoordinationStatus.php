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
 * 障害福祉サービス：明細書：上限管理区分.
 *
 * @method static DwsBillingStatementCopayCoordinationStatus unapplicable() 不要（上限管理なし）
 * @method static DwsBillingStatementCopayCoordinationStatus unclaimable() 不要（サービス提供なし）
 * @method static DwsBillingStatementCopayCoordinationStatus uncreated() 未作成
 * @method static DwsBillingStatementCopayCoordinationStatus unfilled() 未入力
 * @method static DwsBillingStatementCopayCoordinationStatus checking() 入力中
 * @method static DwsBillingStatementCopayCoordinationStatus fulfilled() 入力済
 */
final class DwsBillingStatementCopayCoordinationStatus extends Enum
{
    use DwsBillingStatementCopayCoordinationStatusSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'unapplicable' => 11,
        'unclaimable' => 12,
        'uncreated' => 21,
        'unfilled' => 22,
        'checking' => 23,
        'fulfilled' => 31,
    ];
}
