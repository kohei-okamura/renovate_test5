<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Pdf;

use SplFileInfo;

/**
 * PDF 生成サービス.
 */
interface PdfCreator
{
    /**
     * PDF を生成する.
     *
     * @param string $template
     * @param array $params
     * @param string $orientation
     * @return \SplFileInfo
     */
    public function create(string $template, array $params, string $orientation = 'portrait'): SplFileInfo;
}
