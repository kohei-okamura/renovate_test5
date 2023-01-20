<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Job;

use Domain\Enum;

/**
 * 非同期ジョブ：状態.
 *
 * @method static JobStatus waiting() 待機中
 * @method static JobStatus inProgress() 処理中
 * @method static JobStatus success() 成功
 * @method static JobStatus failure() 失敗
 */
final class JobStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'waiting' => 1,
        'inProgress' => 2,
        'success' => 3,
        'failure' => 9,
    ];
}
