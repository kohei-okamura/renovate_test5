<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Seq;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 利用者請求の入金日が更新可能であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingDepositCanUpdateRule
{
    /**
     * 検証処理.
     *
     * 以下の条件を満たす場合に入金日の更新日が可能
     * - 支払い方法が口座振替以外
     * - 入金日が null である
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingDepositCanUpdate(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_billing_deposit_can_update');
        $permission = Permission::from((string)$parameters[0]);
        return $this->entityExists($value, function (Seq $seq) use ($permission): bool {
            /** @var \UseCase\UserBilling\LookupUserBillingUseCase $useCase */
            $useCase = app(LookupUserBillingUseCase::class);
            $entityCount = $useCase->handle($this->context, $permission, ...$seq->toArray())
                ->filter(function (UserBilling $x): bool {
                    return $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal()
                        && $x->depositedAt === null;
                })
                ->count();
            return $entityCount === $seq->count();
        });
    }
}
