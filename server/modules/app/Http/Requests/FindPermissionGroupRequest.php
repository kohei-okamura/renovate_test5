<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

/**
 * 権限グループ一覧取得リクエスト.
 */
class FindPermissionGroupRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [];
    }
}
