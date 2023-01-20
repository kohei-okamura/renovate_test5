<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations;

use Domain\Permission\Permission;
use Domain\Validator\CreateWithdrawalTransactionAsyncValidator;
use ScalikePHP\Seq;

/**
 * 勤務シフト一括確定非同期処理用バリデータ実装.
 */
class CreateWithdrawalTransactionAsyncValidatorImpl extends AsyncValidatorImpl implements CreateWithdrawalTransactionAsyncValidator
{
    /** {@inheritdoc} */
    protected function rules(): array
    {
        return [
            'userBillingIds' => ['user_billing_whose_amount_greater_than_zero_exists:' . Permission::createWithdrawalTransactions()],
        ];
    }

    /** {@inheritdoc} */
    protected function errorMessage(CustomValidator $validator): Seq
    {
        return Seq::fromArray($validator->errors()->keys())
            ->flatMap(fn (string $key): array => $validator->errors()->get($key));
    }
}
