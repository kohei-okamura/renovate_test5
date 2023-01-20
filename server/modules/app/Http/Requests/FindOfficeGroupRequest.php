<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

/**
 * 事業所グループ検索リクエスト.
 */
class FindOfficeGroupRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [];
    }
}
