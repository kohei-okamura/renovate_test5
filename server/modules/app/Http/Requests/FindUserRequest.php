<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;

/**
 * 利用者検索リクエスト.
 */
class FindUserRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function boolParams(): array
    {
        return ['isEnabled'];
    }

    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return ['officeId', 'q', 'isEnabled'];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            ...parent::rules($input),
            'officeId' => ['nullable', 'office_exists:' . Permission::listUsers()],
            'isEnabled' => ['boolean_ext'],
        ];
    }
}
