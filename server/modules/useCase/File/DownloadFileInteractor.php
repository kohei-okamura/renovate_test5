<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use Domain\File\FileStorage;
use ScalikePHP\Option;

/**
 * ファイルダウンロード実装.
 */
final class DownloadFileInteractor implements DownloadFileUseCase
{
    private FileStorage $fileStorage;

    /**
     * Constructor.
     *
     * @param \Domain\File\FileStorage $fileStorage
     */
    public function __construct(FileStorage $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $path): Option
    {
        return $this->fileStorage->fetchStream($path);
    }
}
