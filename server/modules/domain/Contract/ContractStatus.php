<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Contract;

use Domain\Enum;

/**
 * 契約：状態.
 *
 * @method static ContractStatus provisional() 仮契約
 * @method static ContractStatus formal() 本契約
 * @method static ContractStatus terminated() 契約終了
 * @method static ContractStatus disabled() 無効
 */
final class ContractStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'provisional' => 1,
        'formal' => 2,
        'terminated' => 3,
        'disabled' => 9,
    ];
}
