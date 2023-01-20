<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 障害福祉サービス：ベースアップ等支援加算.
 *
 * @method static DwsBaseIncreaseSupportAddition none() なし
 * @method static DwsBaseIncreaseSupportAddition addition1() 福祉・介護職員等ベースアップ等支援加算
 */
final class DwsBaseIncreaseSupportAddition extends Enum
{
    use DwsBaseIncreaseSupportAdditionSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
    ];
}
