<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

/**
 * ファイル名生成ユースケース.
 */
interface GenerateFileNameUseCase
{
    /**
     * ファイル名を生成する.
     *
     * @param string $filename zinger.php に定義してあるファイル名（zinger.filename. は不要）
     * @param array $replaceKeyValues [プレースホルダー => 値] の連想配列（プレースホルダーに ${} は不要）
     * @return string
     */
    public function handle(string $filename, array $replaceKeyValues = []): string;
}
