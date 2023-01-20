<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Barryvdh\Snappy\PdfWrapper;
use Domain\File\TemporaryFiles;
use Domain\Pdf\PdfCreator;
use Illuminate\View\Factory;
use SplFileInfo;

/**
 * {@link \Domain\Pdf\PdfCreator} 実装.
 */
final class PdfCreatorImpl implements PdfCreator
{
    private TemporaryFiles $temporaryFiles;

    /**
     * {@link \App\Concretes\PdfCreatorImpl} constructor.
     *
     * @param \Domain\File\TemporaryFiles $temporaryFiles
     */
    public function __construct(TemporaryFiles $temporaryFiles)
    {
        $this->temporaryFiles = $temporaryFiles;
    }

    /** {@inheritdoc} */
    public function create(string $template, array $params, string $orientation = 'portrait'): SplFileInfo
    {
        $file = $this->temporaryFiles->create('pdf-', '.pdf');
        self::pdf()
            ->setOption('enable-local-file-access', true)
            ->loadHTML(self::view()->make($template, $params))
            ->setPaper('A4', $orientation)
            ->save($file->getPathname(), true);
        return $file;
    }

    /**
     * Barryvdh\Snappy\PdfWrapper.
     *
     * @return \Barryvdh\Snappy\PdfWrapper
     */
    private static function pdf(): PdfWrapper
    {
        return app('snappy.pdf.wrapper');
    }

    /**
     * Illuminate\View\Factory.
     *
     * @return \Illuminate\View\Factory
     */
    private static function view(): Factory
    {
        return app('view');
    }
}
