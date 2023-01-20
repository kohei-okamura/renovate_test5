<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

return [
    'pdf' => [
        'enabled' => true,
        'binary' => env('SNAPPY_PDF_BINARY', '/usr/bin/wkhtmltopdf'),
        'timeout' => 3600,
        'options' => [],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => env('SNAPPY_IMAGE_BINARY', '/usr/bin/wkhtmltoimage'),
        'timeout' => 3600,
        'options' => [],
        'env' => [],
    ],
];
