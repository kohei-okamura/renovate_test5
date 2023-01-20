<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

/**
 * スタッフ距離情報検索リクエスト.
 */
class FindStaffDistanceRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [
            'location',
            'range',
            'sex',
        ];
    }
}
