<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\File\FileStorage;
use ScalikePHP\Option;

/**
 * ファイルアップロードユースケース実装.
 */
final class UploadStorageInteractor implements UploadStorageUseCase
{
    private FileStorage $storage;

    /**
     * {@\UseCase\File\UploadInteractor} constructor.
     *
     * @param \Domain\File\FileStorage $storage
     */
    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Context $context, string $dir, FileInputStream $stream): Option
    {
        return $this->storage->store($dir, $stream);
    }
}
