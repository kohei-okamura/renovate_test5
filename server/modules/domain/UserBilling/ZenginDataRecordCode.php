<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Enum;

/**
 * 全銀レコード：データレコード：新規コード.
 *
 * @method static ZenginDataRecordCode firstTime() 初回
 * @method static ZenginDataRecordCode change() 変更
 * @method static ZenginDataRecordCode other() その他
 */
final class ZenginDataRecordCode extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'firstTime' => 1,
        'change' => 2,
        'other' => 0,
    ];
}
