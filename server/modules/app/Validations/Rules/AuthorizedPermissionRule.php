<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;

/**
 * 入力値の権限を持っていることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait AuthorizedPermissionRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value RoleID
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateAuthorizedPermission(string $attribute, $value, array $parameters): bool
    {
        // 入力値が不正の場合、このバリデーションではエラーとしない
        if (!Permission::isValid($value)) {
            return true;
        }
        return $this->context->isAuthorizedTo(Permission::from($value));
    }
}
