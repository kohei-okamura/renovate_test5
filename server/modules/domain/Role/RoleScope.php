<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Role;

use Domain\Enum;

/**
 * 権限範囲.
 *
 * @method static RoleScope whole() 全体
 * @method static RoleScope group() グループ
 * @method static RoleScope office() 事業所
 * @method static RoleScope person() 個人
 */
final class RoleScope extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'whole' => 1,
        'group' => 2,
        'office' => 3,
        'person' => 4,
    ];
}
