<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\File;

use Domain\File\FileInputStream;
use Domain\File\FileStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use ScalikePHP\Option;

/**
 * ファイル保管ストレージ実装.
 */
final class FileStorageImpl extends ReadonlyFileStorageImpl implements FileStorage
{
    /** {@inheritdoc} */
    public function store(string $dir, FileInputStream $inputStream): Option
    {
        $path = rtrim($dir . '/' . $inputStream->name(), '/');
        return $this->storage()->put($path, $inputStream->stream()) ? Option::from($path) : Option::none();
    }

    /** {@inheritdoc} */
    protected function storage(): Filesystem
    {
        $disk = $this->config->get('zinger.file.storage');
        return $this->filesystem->disk($disk);
    }
}
