<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\LtcsInsCard;

use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Eloquent 用 {@link \Domain\LtcsInsCard\LtcsCarePlanAuthorType} 相互変換処理.
 */
final class CastsLtcsCarePlanAuthorType implements CastsAttributes
{
    /** {@inheritdoc} */
    public function get($model, string $key, $value, array $attributes): LtcsCarePlanAuthorType
    {
        return LtcsCarePlanAuthorType::from($value);
    }

    /** {@inheritdoc} */
    public function set($model, string $key, $value, array $attributes): int
    {
        assert($value instanceof LtcsCarePlanAuthorType);
        return $value->value();
    }
}
