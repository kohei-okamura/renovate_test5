<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\File;

use SplFileInfo;

/**
 * 一時ファイル作成.
 */
interface TemporaryFiles
{
    /**
     * 一時ファイルを作成する.
     *
     * @param string $prefix
     * @param string $suffix
     * @return \SplFileInfo
     */
    public function create(string $prefix, string $suffix): SplFileInfo;
}
