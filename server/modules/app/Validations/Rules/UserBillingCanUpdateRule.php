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
use Illuminate\Support\Arr;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 指定された利用者請求が更新可能であるか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingCanUpdateRule
{
    /**
     * 検証処理.
     *
     * 以下の条件を全て満たす場合に利用者請求が更新可能
     * - 請求結果が未処理または請求なし
     * - 口座振替データが生成済みでない
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingCanUpdate(string $attribute, $value, array $parameters): bool
    {
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

        return ($userBilling->result === UserBillingResult::pending() || $userBilling->result === UserBillingResult::none())
            && $userBilling->transactedAt === null;
    }
}
