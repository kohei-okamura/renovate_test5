<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use SplFileInfo;

/**
 * ファイルダウンロードユースケース.
 */
interface DownloadStorageUseCase
{
    /**
     * ファイルをストレージからダウンロードする.
     *
     * @param \Domain\Context\Context $context
     * @param string $storagePath
     * @return SplFileInfo
     */
    public function handle(Context $context, string $storagePath): SplFileInfo;
}
