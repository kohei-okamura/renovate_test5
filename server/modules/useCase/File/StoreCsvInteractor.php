<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\File\FileStorage;
use Domain\File\TemporaryFiles;
use Lib\Csv;
use Lib\Exceptions\FileIOException;
use Lib\StreamFilter\StreamFilter;

/**
 * CSV ファイルを生成してファイルストレージに格納するユースケース実装.
 */
final class StoreCsvInteractor implements StoreCsvUseCase
{
    private FileStorage $fileStorage;
    private TemporaryFiles $temporaryFiles;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor} constructor.
     *
     * @param \Domain\File\FileStorage $fileStorage
     * @param \Domain\File\TemporaryFiles $temporaryFiles
     */
    public function __construct(
        FileStorage $fileStorage,
        TemporaryFiles $temporaryFiles
    ) {
        $this->fileStorage = $fileStorage;
        $this->temporaryFiles = $temporaryFiles;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $dir, string $prefix, iterable $rows): string
    {
        return $this->store($dir, $this->createCsv($prefix, $rows));
    }

    /**
     * CSV ファイルを生成してそのパスを返す.
     *
     * @param string $prefix
     * @param iterable $rows
     * @return string
     */
    private function createCsv(string $prefix, iterable $rows): string
    {
        $path = $this->temporaryFiles->create($prefix, '.csv')->getPathname();
        $stream = StreamFilter::pathBuilder()
            ->withResource($path)
            ->withWriteFilter(StreamFilter::crlf())
            ->withWriteFilter(StreamFilter::iconv('utf-8', 'cp932'))
            ->build();
        Csv::write($stream, $rows);
        return $path;
    }

    /**
     * CSV ファイルをファイルストレージに格納してそのパスを返す.
     *
     * @param string $dir
     * @param string $source
     * @return string
     */
    private function store(string $dir, string $source): string
    {
        $inputStream = FileInputStream::from(basename($source), $source);
        return $this->fileStorage->store($dir, $inputStream)->getOrElse(function () use ($source): void {
            throw new FileIOException("Failed to store file: {$source}");
        });
    }
}
