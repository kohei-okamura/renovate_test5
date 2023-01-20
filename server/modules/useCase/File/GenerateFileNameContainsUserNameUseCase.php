<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;

/**
 * 利用者名を含むファイル名生成ユースケース.
 */
interface GenerateFileNameContainsUserNameUseCase
{
    /**
     * 利用者名を含むファイル名を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param string $filename zinger.php に定義してあるファイル名（zinger.filename. は不要）
     * @param array $replaceKeyValues [プレースホルダー => 値] の連想配列（プレースホルダーに ${} は不要）
     * @return string
     */
    public function handle(Context $context, int $userId, string $filename, array $replaceKeyValues = []): string;
}
