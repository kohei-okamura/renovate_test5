<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

/**
 * 全銀仕様データレコード使用可能文字か検証する.
 * @link https://www.tanshin.co.jp/business/netbk/pdf/zengin_moji.pdf
 *
 * @mixin \App\Validations\CustomValidator
 */
trait ZenginDataRecordCharRule
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
    protected function validateZenginDataRecordChar(string $attribute, $value, array $parameters): bool
    {
        return (bool)preg_match('/\A[0-9０-９a-zA-Zａ-ｚＡ-Ｚｦ-ﾟァ-ヶ 　（）()｢｣「」\/／．.\-¥￥]*\z/u', $value);
    }
}
