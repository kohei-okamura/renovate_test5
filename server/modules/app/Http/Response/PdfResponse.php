<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Response;

use Barryvdh\Snappy\PdfWrapper;
use Illuminate\View\Factory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * PDF Response builder.
 *
 * @codeCoverageIgnore
 */
final class PdfResponse
{
    /**
     * Create 200 Ok Response.
     *
     * @param string $view view filepath string
     * @param array $values
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function ok(string $view, array $values, string $filename): SymfonyResponse
    {
        $pdf = self::pdf()->setOption('enable-local-file-access', true);
        return $pdf->loadHTML(self::view()->make($view, $values))->download($filename);
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
