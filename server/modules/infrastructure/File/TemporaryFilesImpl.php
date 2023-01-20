<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\File;

use Domain\Config\Config;
use Domain\File\TemporaryFiles;
use Lib\Exceptions\TemporaryFileAccessException;
use Lib\RandomString;
use SplFileInfo;

/**
 * 一時ファイル作成器実装.
 */
class TemporaryFilesImpl implements TemporaryFiles
{
    private const FILENAME_LENGTH = 16;

    private Config $config;

    /**
     * TemporaryFileCreatorImpl constructor.
     *
     * @param \Domain\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function create(string $prefix, string $suffix): SplFileInfo
    {
        $dir = $this->config->get('zinger.path.temp');
        $path = RandomString::seq(self::FILENAME_LENGTH)
            ->map(fn (string $name): string => $dir . '/' . $prefix . $name . $suffix)
            ->find(fn (string $path): bool => !file_exists($path))
            ->getOrElse(function (): void {
                throw new TemporaryFileAccessException('Failed to create temporary file'); // @codeCoverageIgnore
            });
        touch($path);
        chmod($path, 0600);
        return new SplFileInfo($path);
    }
}
