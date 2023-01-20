<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Seq;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 指定された「利用者請求」を契約者番号ごとにまとめて、請求金額が0円より大きいものが存在するか検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingWhoseAmountGreaterThanZeroExistsRule
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
    protected function validateUserBillingWhoseAmountGreaterThanZeroExists(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_billing_whose_amount_greater_than_zero_exists');
        $permission = Permission::from((string)$parameters[0]);

        // 存在しない利用者請求が含まれる場合、ここではエラーとしない
        if (!$this->validateUserBillingExists($attribute, $value, [$permission])) {
            return true;
        }

        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);

        return $lookupUserBillingUseCase
            ->handle($this->context, $permission, ...$value)
            ->groupBy(fn (UserBilling $x): string => $x->user->billingDestination->contractNumber)
            ->mapValues(
                fn (Seq $userBillings) => $userBillings->map(fn (UserBilling $x): int => $x->totalAmount)->sum()
            )
            ->exists(fn (int $x): bool => $x > 0);
    }
}
