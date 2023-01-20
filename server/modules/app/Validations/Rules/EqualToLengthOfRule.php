<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Illuminate\Support\Arr;

/**
 * 入力値が指定の配列の長さと等しいことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait EqualToLengthOfRule
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
    protected function validateEqualToLengthOf(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'equal_to_length_of');
        $array = Arr::get($this->data, $parameters[0]);
        if (!is_array($array)) {
            return false;
        }
        return $value === count($array);
    }
}
