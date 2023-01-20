<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

/**
 * 入力値が正しい電話番号であることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait PhoneNumberRule
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
    protected function validatePhoneNumber(string $attribute, $value, array $parameters): bool
    {
        $pattern = '/\A(?:0(?:[1-9]-?[1-9]\d{3}|[1-9]{2}-?[1-9]\d{2}|[1-9]{2}\d-?[1-9]\d|[1-9]{2}\d{2}-?[1-9])-?\d{4}|0\d{2}0-?\d{3}-?\d{3}|0\d{2}0-?\d{2}-?\d{4}|0[5789]0-?\d{4}-?\d{4})\z/';
        return (bool)preg_match($pattern, $value);
    }
}
