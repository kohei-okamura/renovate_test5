<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\File;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Domain\File\ReadonlyFileStorage;
use Domain\File\TemporaryFiles;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Laravel\Lumen\Application;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as LeagueFilesystem;
use RuntimeException;
use ScalikePHP\Option;
use SplFileInfo;

/**
 * 読み取り専用ファイル保管ストレージ実装.
 */
class ReadonlyFileStorageImpl implements ReadonlyFileStorage
{
    protected const TEMPORARY_FILE_PREFIX = 'zinger-';

    protected Config $config;
    protected FilesystemManager $filesystem;
    protected TemporaryFiles $temporaryFiles;

    /**
     * {@link \Infrastructure\File\FileStorageImpl} constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \Domain\File\TemporaryFiles $temporaryFiles
     */
    public function __construct(Config $config, TemporaryFiles $temporaryFiles)
    {
        $this->config = $config;
        $this->filesystem = app('filesystem');
        $this->temporaryFiles = $temporaryFiles;
    }

    /** {@inheritdoc} */
    public function fetch(string $path): Option
    {
        return $this->fetchStream($path)->map(function ($inputStream) use ($path): SplFileInfo {
            try {
                $output = $this->createTemporaryFile('.' . pathinfo($path, \PATHINFO_EXTENSION));
                $this->local()->putStream($output->getPathname(), $inputStream);
                return $output;
            } finally {
                fclose($inputStream);
            }
        });
    }

    /** {@inheritdoc} */
    public function fetchStream(string $path): Option
    {
        try {
            return Option::from($this->storage()->readStream($path));
        } catch (FileNotFoundException $exception) {
            return Option::none();
        }
    }

    /** {@inheritdoc} */
    public function getTemporaryUrl(string $path, Carbon $expiration, string $filename): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = $this->storage();
        $encodeFilename = rawurlencode($filename);
        try {
            return $storage->temporaryUrl($path, $expiration, [
                'ResponseContentDisposition' => "attachment; filename=\"[{$encodeFilename}]\"; filename*=UTF-8''{$encodeFilename}",
            ]);
        } catch (RuntimeException $e) {
            if (Application::getInstance()->environment() === 'production') {
                throw $e;
            } else {
                // NOTE: テスト用
                return $this->config->get('app.url') . $storage->url($path);
            }
        }
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function storage(): Filesystem
    {
        $disk = $this->config->get('zinger.file.readonly_storage');
        return $this->filesystem->disk($disk);
    }

    /**
     * @param string $suffix
     * @return \SplFileInfo
     */
    private function createTemporaryFile(string $suffix): SplFileInfo
    {
        return $this->temporaryFiles->create(self::TEMPORARY_FILE_PREFIX, $suffix);
    }

    /**
     * @return \League\Flysystem\Filesystem
     */
    private function local(): LeagueFilesystem
    {
        $adapter = new Local('/');
        return new LeagueFilesystem($adapter);
    }
}
