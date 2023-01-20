<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use Domain\File\FileInputStream;
use ScalikePHP\Option;

/**
 * ファイルアップロードユースケース.
 */
interface UploadStorageUseCase
{
    /**
     * ファイルをアップロードする.
     *
     * @param \Domain\Context\Context $context
     * @param string $dir
     * @param \Domain\File\FileInputStream $file
     * @return \ScalikePHP\Option|string[]
     */
    public function handle(Context $context, string $dir, FileInputStream $file): Option;
}
