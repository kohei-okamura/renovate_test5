<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * MimeType.
 *
 * @method static MimeType csv() CSV
 * @method static MimeType pdf() PDF
 */
final class MimeType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'csv' => 'text/csv',
        'pdf' => 'application/pdf',
    ];
}
