<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 事業所：状態.
 *
 * @method static OfficeStatus inPreparation() 準備中
 * @method static OfficeStatus inOperation() 運営中
 * @method static OfficeStatus suspended() 休止
 * @method static OfficeStatus closed() 廃止
 */
final class OfficeStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'inPreparation' => 1,
        'inOperation' => 2,
        'suspended' => 8,
        'closed' => 9,
    ];
}
