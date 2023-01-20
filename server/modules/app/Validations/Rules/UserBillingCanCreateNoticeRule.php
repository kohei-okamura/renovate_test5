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
 * 指定された利用者請求が代理受領額通知書がダウンロード可能であるか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingCanCreateNoticeRule
{
    /**
     * 検証処理.
     *
     * 以下の条件を全て満たす場合に利用者請求が更新可能
     * - 障害福祉サービス明細が存在している
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateUserBillingCanCreateNotice(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_billing_can_create_notice');
        $permission = Permission::from((string)$parameters[0]);
        // 配列でない場合このバリデーションではエラーとしない
        if (!is_array($value)) {
            return true;
        }
        // 存在しないIDを含む場合、このバリデーションではエラーとしない
        /** @var \UseCase\UserBilling\LookupUserBillingUseCase $useCase */
        $useCase = app(LookupUserBillingUseCase::class);
        $userBillings = $useCase->handle($this->context, $permission, ...$value);
        if ($userBillings->isEmpty()) {
            return true;
        }
        return $userBillings->forAll(function (UserBilling $x): bool {
            return $x->dwsItem !== null;
        });
    }
}
