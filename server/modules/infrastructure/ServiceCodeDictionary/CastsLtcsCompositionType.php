<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsCompositionType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\ServiceCodeDictionary\LtcsCompositionType} 相互変換処理.
 */
final class CastsLtcsCompositionType implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): LtcsCompositionType
    {
        return LtcsCompositionType::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof LtcsCompositionType);
        return $value->value();
    }
}
