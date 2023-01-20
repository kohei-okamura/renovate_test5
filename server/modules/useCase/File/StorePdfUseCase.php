<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;

/**
 * PDF ファイルを生成してファイルストレージに格納するユースケース.
 */
interface StorePdfUseCase
{
    /**
     * PDF ファイルを生成してファイルストレージに格納し、格納されたパスを返す.
     *
     * @param \Domain\Context\Context $context
     * @param string $dir
     * @param string $template
     * @param array $params
     * @param string $orientation
     * @return string
     */
    public function handle(Context $context, string $dir, string $template, array $params, string $orientation = 'portrait'): string;
}
