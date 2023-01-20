<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * ファイルダウンロードユースケース.
 */
interface DownloadFileUseCase
{
    /**
     * ファイルをダウンロードする.
     *
     * @param \Domain\Context\Context $context
     * @param string $path
     * @return resource[]|\ScalikePHP\Option
     */
    public function handle(Context $context, string $path): Option;
}
