<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 指定された利用者請求に入金日が登録されているか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingDepositRequiredRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingDepositRequired(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_billing_deposit_required');

        // 配列でない場合このバリデーションではエラーとしない
        if (!is_array($value)) {
            return true;
        }

        $permission = Permission::from((string)$parameters[0]);
        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);
        $userBillings = $lookupUserBillingUseCase->handle($this->context, $permission, ...$value);

        // 存在しないIDを含む場合、このバリデーションではエラーとしない
        if ($userBillings->isEmpty()) {
            return true;
        }

        return $userBillings->forAll(fn (UserBilling $x): bool => $x->depositedAt !== null);
    }
}
