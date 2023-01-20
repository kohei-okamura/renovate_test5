<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

/**
 * 入力値が半角英数字であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait AsciiAlphaNumRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateAsciiAlphaNum(string $attribute, mixed $value, array $parameters): bool
    {
        return ctype_alnum($value);
    }
}
