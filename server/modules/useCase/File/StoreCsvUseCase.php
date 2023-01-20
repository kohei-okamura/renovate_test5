<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;

/**
 * CSV ファイルを生成してファイルストレージに格納するユースケース.
 */
interface StoreCsvUseCase
{
    /**
     * CSV ファイルを生成してファイルストレージに格納し、格納されたパスを返す.
     *
     * @param \Domain\Context\Context $context
     * @param string $dir
     * @param string $prefix
     * @param iterable $rows
     * @return string
     */
    public function handle(Context $context, string $dir, string $prefix, iterable $rows): string;
}
