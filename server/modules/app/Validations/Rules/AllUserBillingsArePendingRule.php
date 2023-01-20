<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 指定された「利用者請求」に口座振替データ生成済みのものが含まれていないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait AllUserBillingsArePendingRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateAllUserBillingsArePending(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'no_user_billings_transacted');
        $permission = Permission::from((string)$parameters[0]);

        // 存在しない利用者請求が含まれる場合、ここではエラーとしない
        if (!$this->validateUserBillingExists($attribute, $value, [$permission])) {
            return true;
        }

        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);
        $userBillings = $lookupUserBillingUseCase->handle($this->context, $permission, ...$value);

        return $userBillings->forAll(fn (UserBilling $x): bool => $x->result === UserBillingResult::pending());
    }
}
