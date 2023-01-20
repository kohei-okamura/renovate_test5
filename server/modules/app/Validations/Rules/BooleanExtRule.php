<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

/**
 * 入力値がbooleanであることを検証する（拡張版）.
 * see https://github.com/laravel/framework/blob/v8.1.0/src/Illuminate/Validation/Concerns/ValidatesAttributes.php#L332
 *
 * @mixin \App\Validations\CustomValidator
 */
trait BooleanExtRule
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
    protected function validateBooleanExt(string $attribute, $value, array $parameters): bool
    {
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];

        return in_array($value, $acceptable, true);
    }
}
