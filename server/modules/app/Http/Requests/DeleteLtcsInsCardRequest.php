<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 介護保険被保険者証削除リクエスト.
 *
 * @property-read int $id
 */
class DeleteLtcsInsCardRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'id' => [
                'ltcs_ins_card_not_belong_to_billing:userId,' . Permission::deleteLtcsInsCards(),
            ],
        ];
    }
}
