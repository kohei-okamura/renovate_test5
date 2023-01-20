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
use Domain\Pdf\PdfCreator;
use Lib\Exceptions\FileIOException;

/**
 * PDF ファイルを生成してファイルストレージに格納するユースケース実装.
 */
final class StorePdfInteractor implements StorePdfUseCase
{
    private FileStorage $fileStorage;
    private TemporaryFiles $temporaryFiles;
    private PdfCreator $pdfCreator;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor} constructor.
     *
     * @param \Domain\File\FileStorage $fileStorage
     * @param \Domain\Pdf\PdfCreator $pdfCreator
     */
    public function __construct(
        FileStorage $fileStorage,
        PdfCreator $pdfCreator
    ) {
        $this->fileStorage = $fileStorage;
        $this->pdfCreator = $pdfCreator;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $dir, string $template, array $params, string $orientation = 'portrait'): string
    {
        return $this->store($dir, $this->createPdf($template, $params, $orientation));
    }

    /**
     * PDF ファイルを生成してそのパスを返す.
     *
     * @param string $template
     * @param array $params
     * @param string $orientation
     * @return string
     */
    private function createPdf(string $template, array $params, string $orientation): string
    {
        return $this->pdfCreator->create($template, $params, $orientation)->getPathname();
    }

    /**
     * PDF ファイルをファイルストレージに格納してそのパスを返す.
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
