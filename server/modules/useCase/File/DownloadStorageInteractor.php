<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use Domain\File\FileStorage;
use Lib\Exceptions\TemporaryFileAccessException;
use SplFileInfo;

/**
 * ファイルダウンロード実装.
 */
class DownloadStorageInteractor implements DownloadStorageUseCase
{
    private FileStorage $storage;

    /**
     * constructor.
     * @param \Domain\File\FileStorage $storage
     */
    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, string $storagePath): SplFileInfo
    {
        return $this->storage
            ->fetch($storagePath)
            ->getOrElse(function () use ($storagePath): void {
                throw new TemporaryFileAccessException("StoragePath[{$storagePath}]");
            });
    }
}
