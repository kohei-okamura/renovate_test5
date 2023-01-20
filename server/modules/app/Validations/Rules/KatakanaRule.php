<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

/**
 * 入力値がカタカナのみで構成されていることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait KatakanaRule
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
    protected function validateKatakana(string $attribute, $value, array $parameters): bool
    {
        return (bool)preg_match('/\A[ァ-ヶー]+\z/u', $value);
    }
}
