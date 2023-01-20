<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * 入力値がExcelタイムスタンプであることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait ExcelTimestampRule
{
    /**
     * 入力値がExcelタイムスタンプであることを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateExcelTimestamp(string $attribute, $value, array $parameters): bool
    {
        if (!is_int($value) && !is_float($value)) {
            return false;
        }
        try {
            Date::excelToDateTimeObject($value);
        } catch (ErrorException $exception) {
            return false;
        }
        return true;
    }
}
