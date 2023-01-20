<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Domain\Permission\Permission;
use Domain\Validator\ConfirmShiftAsyncValidator;
use ScalikePHP\Seq;

/**
 * 勤務シフト一括確定非同期処理用バリデータ実装.
 */
class ConfirmShiftAsyncValidatorImpl extends AsyncValidatorImpl implements ConfirmShiftAsyncValidator
{
    /** {@inheritdoc} */
    protected function rules(): array
    {
        return [
            'ids.*' => ['no_conflict:ids,' . Permission::updateShifts()],
        ];
    }

    /** {@inheritdoc} */
    protected function errorMessage(CustomValidator $validator): Seq
    {
        // TODO: DEV-2777 エラーメッセージを検討
        return Seq::fromArray($validator->errors()->keys())
            ->map(function (string $key) use ($validator) {
                $message = $validator->errors()->get($key)[0];
                return "{$key}は{$message}";
            });
    }
}
