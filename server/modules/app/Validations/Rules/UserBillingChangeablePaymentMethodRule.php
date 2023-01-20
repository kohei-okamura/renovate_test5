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
use Illuminate\Support\Arr;
use UseCase\UserBilling\LookupUserBillingUseCase;

// TODO 変更先が不正でエラーになるパターンが「未設定」「口座振替」と2種類あるので、エラーメッセージを動的に変更したい

/**
 * 指定された支払方法への変更が可能か検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingChangeablePaymentMethodRule
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
    protected function validateUserBillingChangeablePaymentMethod(string $attribute, $value, array $parameters): bool
    {
        // 無効な支払方法の場合、このバリデーションではエラーとしない
        if (!PaymentMethod::isValid((int)$value)) {
            return true;
        }

        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);

        $userBilling = $lookupUserBillingUseCase
            ->handle(
                $this->context,
                Permission::updateUserBillings(),
                (int)Arr::get($this->data, 'id', -1)
            )
            ->headOption()
            ->orNull();
        // 存在しないIDの場合、このバリデーションではエラーとしない
        if ($userBilling === null) {
            return true;
        }
        assert($userBilling instanceof UserBilling);

        $paymentMethod = PaymentMethod::from((int)$value);

        return $this->canChange($userBilling->user->billingDestination->paymentMethod, $paymentMethod);
    }

    /**
     * 指定した支払方法への変更が可能かを返す.
     *
     * @param \Domain\User\PaymentMethod $current
     * @param \Domain\User\PaymentMethod $next
     * @return bool
     */
    private function canChange(PaymentMethod $current, PaymentMethod $next): bool
    {
        // 変更なし or 銀行振込 or 集金 への変更は可能
        return $next === $current
            || $next === PaymentMethod::transfer()
            || $next === PaymentMethod::collection();
    }
}
