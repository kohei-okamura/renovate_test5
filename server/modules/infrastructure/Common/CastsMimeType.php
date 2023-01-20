<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\MimeType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\Common\MimeType} 相互変換処理.
 */
final class CastsMimeType implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): MimeType
    {
        return MimeType::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): string
    {
        assert($value instanceof MimeType);
        return $value->value();
    }
}
