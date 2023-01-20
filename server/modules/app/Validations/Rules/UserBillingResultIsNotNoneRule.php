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
 * 指定された利用者請求に請求結果が「請求なし」のものが含まれていないかどうかを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingResultIsNotNoneRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param array|int $value // 利用者請求 ID or 利用者請求 ID の配列
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingResultIsNotNone(string $attribute, $value, array $parameters): bool
    {
        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);

        $ids = is_array($value) ? $value : [$value];
        $userBillings = $lookupUserBillingUseCase->handle($this->context, Permission::viewUserBillings(), ...$ids);

        // 存在しない ID を含む（指定した ID と 取得した利用者請求の件数が異なる）場合、このバリデーションではエラーとしない
        if ($userBillings->size() !== count($ids)) {
            return true;
        }

        return $userBillings->forAll(
            fn (UserBilling $x): bool => $x->result !== UserBillingResult::none()
        );
    }
}
